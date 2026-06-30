<?php

namespace App\Services\GrpcClients;

use Matchmaking\UserServiceClient;
use Matchmaking\ValidatePlayerRequest;

class UserGrpcClient
{
    private UserServiceClient $client;

    public function __construct()
    {
        $this->client = new UserServiceClient(
            'app:50052', // different port from ranking's 50051
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
}