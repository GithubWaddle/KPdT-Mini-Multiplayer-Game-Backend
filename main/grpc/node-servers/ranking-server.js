const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const path = require('path');
const { writePool, readPool, redisClient, initRedis } = require('./db');

// Load the .proto file directly — no generated files needed
const PROTO_PATH = path.join(__dirname, '../protos/matchmaking.proto');
const packageDefinition = protoLoader.loadSync(PROTO_PATH, {
  keepCase: false,    // converts snake_case proto fields to camelCase in JS
  longs: String,
  enums: String,
  defaults: true,
  oneofs: true,
});

const matchmakingProto = grpc.loadPackageDefinition(packageDefinition).matchmaking;

// ── Implement UpdateRanking ──────────────────────────────
async function updateRanking(call, callback) {
  const { winnerId, loserId } = call.request;

  const connection = await writePool.getConnection();

  try {
    await connection.beginTransaction();

    // Lock rows like we did in PHP, prevent race conditions
    const [[winnerRow]] = await connection.query(
      'SELECT * FROM rankings WHERE user_id = ? FOR UPDATE',
      [winnerId]
    );
    const [[loserRow]] = await connection.query(
      'SELECT * FROM rankings WHERE user_id = ? FOR UPDATE',
      [loserId]
    );

    const newWinnerPoints = winnerRow.points + 10;
    const newLoserPoints = Math.max(0, loserRow.points - 5);

    await connection.query(
      'UPDATE rankings SET wins = wins + 1, points = ? WHERE user_id = ?',
      [newWinnerPoints, winnerId]
    );

    await connection.query(
      'UPDATE rankings SET losses = losses + 1, points = ? WHERE user_id = ?',
      [newLoserPoints, loserId]
    );

    await connection.commit();

    // Bust Redis cache — same keys PHP code uses
    await redisClient.del('ranking:leaderboard');
    await redisClient.zAdd('ranking:scores', [
      { score: newWinnerPoints, value: String(winnerId) },
      { score: newLoserPoints, value: String(loserId) },
    ]);

    callback(null, {
      success: true,
      message: 'Ranking updated successfully',
      winnerPoints: newWinnerPoints,
      loserPoints: newLoserPoints,
    });

  } catch (err) {
    await connection.rollback();
    callback(null, {
      success: false,
      message: 'Failed: ' + err.message,
      winnerPoints: 0,
      loserPoints: 0,
    });
  } finally {
    connection.release();
  }
}

// ── Implement GetPlayerRank ──────────────────────────────
async function getPlayerRank(call, callback) {
  const { userId } = call.request;

  try {
    const [[ranking]] = await readPool.query(
      'SELECT * FROM rankings WHERE user_id = ?',
      [userId]
    );

    if (!ranking) {
      return callback(null, { userId, rank: 0, points: 0, wins: 0, losses: 0, winRate: 0 });
    }

    const [[{ count }]] = await readPool.query(
      'SELECT COUNT(*) as count FROM rankings WHERE points > ?',
      [ranking.points]
    );

    const total = ranking.wins + ranking.losses;
    const winRate = total > 0 ? Math.round((ranking.wins / total) * 10000) / 100 : 0;

    callback(null, {
      userId,
      rank: count + 1,
      points: ranking.points,
      wins: ranking.wins,
      losses: ranking.losses,
      winRate,
    });

  } catch (err) {
    callback({ code: grpc.status.INTERNAL, message: err.message });
  }
}

// ── Start the server ──────────────────────────────────────
async function main() {
  await initRedis();

  const server = new grpc.Server();

  server.addService(matchmakingProto.RankingService.service, {
    UpdateRanking: updateRanking,
    GetPlayerRank: getPlayerRank,
  });

  server.bindAsync(
    '0.0.0.0:50051',
    grpc.ServerCredentials.createInsecure(),
    (err, port) => {
      if (err) {
        console.error('Failed to bind:', err);
        return;
      }
      console.log(`Ranking gRPC server running on port ${port}`);
    }
  );
}

main();
