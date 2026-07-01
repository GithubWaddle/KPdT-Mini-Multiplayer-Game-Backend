<?php

namespace App\Services\GrpcClients;

use Matchmaking\UserServiceClient;
use Matchmaking\ValidatePlayerRequest;
use Matchmaking\UpdateUserScoreRequest;    // NEW
use Matchmaking\CompensateUserScoreRequest; // NEW

class UserGrpcClient
{
    private UserServiceClient $client;

    public function __construct()
    {
        $this->client = new UserServiceClient(
            'grpc_user:50052', // different port from ranking's 50051
            ['credentials' => \Grpc\ChannelCredentials::createInsecure()]
        );
    }

    public function validatePlayer(int $userId): array
    {
        $request = new ValidatePlayerRequest();
        $request->setUserId($userId);

        [$response, $status] = $this->client->ValidatePlayer($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC ValidatePlayer failed: ' . $status->details);
        }

        return [
            'exists' => $response->getExists(),
            'name'   => $response->getName(),
            'score'  => $response->getScore(),
        ];
    }

    // NEW
    public function updateUserScore(int $userId, int $newScore): array
    {
        $request = new UpdateUserScoreRequest();
        $request->setUserId($userId);
        $request->setNewScore($newScore);

        [$response, $status] = $this->client->UpdateUserScore($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC UpdateUserScore failed: ' . $status->details);
        }

        return [
            'success'    => $response->getSuccess(),
            'prev_score' => $response->getPrevScore(),
        ];
    }

    // NEW
    public function compensateUserScore(int $userId, int $prevScore): void
    {
        $request = new CompensateUserScoreRequest();
        $request->setUserId($userId);
        $request->setPrevScore($prevScore);

        [$response, $status] = $this->client->CompensateUserScore($request)->wait();

        if ($status->code !== \Grpc\STATUS_OK) {
            throw new \Exception('gRPC CompensateUserScore failed: ' . $status->details);
        }
    }
}
