<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Ranking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Matchmaking\UpdateRankingRequest;
use Matchmaking\UpdateRankingResponse;
use Matchmaking\GetPlayerRankRequest;
use Matchmaking\GetPlayerRankResponse;

// ── Handler functions (plain functions, not a class extending anything) ──

function handleUpdateRanking(UpdateRankingRequest $request): UpdateRankingResponse
{
    $winnerId = $request->getWinnerId();
    $loserId  = $request->getLoserId();

    $response = new UpdateRankingResponse();

    DB::beginTransaction();
    try {
        $winnerRanking = Ranking::where('user_id', $winnerId)->lockForUpdate()->first();
        $loserRanking  = Ranking::where('user_id', $loserId)->lockForUpdate()->first();

        $winnerRanking->increment('wins');
        $winnerRanking->increment('points', 10);

        $loserRanking->increment('losses');
        $loserRanking->points = max(0, $loserRanking->points - 5);
        $loserRanking->save();

        DB::commit();

        Redis::del('ranking:leaderboard');
        Redis::zadd('ranking:scores', $winnerRanking->points, $winnerId);
        Redis::zadd('ranking:scores', $loserRanking->points, $loserId);

        $response->setSuccess(true);
        $response->setMessage('Ranking updated successfully');
        $response->setWinnerPoints($winnerRanking->points);
        $response->setLoserPoints($loserRanking->points);

    } catch (\Exception $e) {
        DB::rollBack();
        $response->setSuccess(false);
        $response->setMessage('Failed: ' . $e->getMessage());
    }

    return $response;
}

function handleGetPlayerRank(GetPlayerRankRequest $request): GetPlayerRankResponse
{
    $userId  = $request->getUserId();
    $ranking = Ranking::where('user_id', $userId)->first();

    $response = new GetPlayerRankResponse();

    if (!$ranking) {
        $response->setUserId($userId);
        $response->setRank(0);
        return $response;
    }

    $rank = Ranking::where('points', '>', $ranking->points)->count() + 1;

    $response->setUserId($userId);
    $response->setRank($rank);
    $response->setPoints($ranking->points);
    $response->setWins($ranking->wins);
    $response->setLosses($ranking->losses);
    $response->setWinRate($ranking->win_rate);

    return $response;
}

// ── Raw server setup, manually wiring methods to the service path ──

$server = new Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:50051');

// Path format is always: /<package>.<ServiceName>/<MethodName>
// matches exactly what's declared in the .proto file
$server->handle(
    '/matchmaking.RankingService/UpdateRanking',
    function ($requestBytes) {
        $request = new UpdateRankingRequest();
        $request->mergeFromString($requestBytes); // decode incoming bytes into PHP object

        $response = handleUpdateRanking($request);

        return $response->serializeToString(); // encode PHP object back into bytes
    }
);

$server->handle(
    '/matchmaking.RankingService/GetPlayerRank',
    function ($requestBytes) {
        $request = new GetPlayerRankRequest();
        $request->mergeFromString($requestBytes);

        $response = handleGetPlayerRank($request);

        return $response->serializeToString();
    }
);

echo "Ranking gRPC server running on port 50051...\n";
$server->run();