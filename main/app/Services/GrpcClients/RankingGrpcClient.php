<?php

namespace App\Services\GrpcClients;

use Matchmaking\RankingServiceClient;
use Matchmaking\UpdateRankingRequest;
use Matchmaking\GetPlayerRankRequest;
use Matchmaking\CompensateRankingRequest; // NEW

class RankingGrpcClient
{
    private RankingServiceClient $client;

    public function __construct()
    {
        // ranking_service = docker-compose service name
        // 50051 = standard gRPC port
        $this->client = new RankingServiceClient(
            'grpc_ranking:50051',
            ['credentials' => \Grpc\ChannelCredentials::createInsecure()]
        );
    }

    public function updateRanking(int $winnerId, int $loserId, int $matchId): array
    {
        $request = new UpdateRankingRequest();
        $request->setWinnerId($winnerId);
        $request->setLoserId($loserId);
        $request->setMatchId($matchId);

        // gRPC call — synchronous wait
        [$response, $status] = $this->client->UpdateRanking($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC UpdateRanking failed: ' . $status->details);
        }

        return [
            'success'       => $response->getSuccess(),
            'message'       => $response->getMessage(),
            'winner_points' => $response->getWinnerPoints(),
            'loser_points'  => $response->getLoserPoints(),
        ];
    }

    // NEW
    public function compensateRanking(int $winnerId, int $loserId, int $winnerPoints, int $loserPoints): void
    {
        $request = new CompensateRankingRequest();
        $request->setWinnerId($winnerId);
        $request->setLoserId($loserId);
        $request->setWinnerPoints($winnerPoints);
        $request->setLoserPoints($loserPoints);

        [$response, $status] = $this->client->CompensateRanking($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC CompensateRanking failed: ' . $status->details);
        }
    }

    public function getPlayerRank(int $userId): array
    {
        $request = new GetPlayerRankRequest();
        $request->setUserId($userId);

        [$response, $status] = $this->client->GetPlayerRank($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC GetPlayerRank failed: ' . $status->details);
        }

        return [
            'user_id'  => $response->getUserId(),
            'rank'     => $response->getRank(),
            'points'   => $response->getPoints(),
            'wins'     => $response->getWins(),
            'losses'   => $response->getLosses(),
            'win_rate' => $response->getWinRate(),
        ];
    }
}
