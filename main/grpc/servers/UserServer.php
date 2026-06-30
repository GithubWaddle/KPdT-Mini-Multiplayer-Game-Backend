<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Matchmaking\ValidatePlayerRequest;
use Matchmaking\ValidatePlayerResponse;

function handleValidatePlayer(ValidatePlayerRequest $request): ValidatePlayerResponse
{
    $userId = $request->getUserId();
    $user   = User::find($userId);

    $response = new ValidatePlayerResponse();

    if (!$user) {
        $response->setExists(false);
        return $response;
    }

    $response->setExists(true);
    $response->setName($user->name);
    $response->setScore($user->score);

    return $response;
}

$server = new Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:50052');

$server->handle(
    '/matchmaking.UserService/ValidatePlayer',
    function ($requestBytes) {
        $request = new ValidatePlayerRequest();
        $request->mergeFromString($requestBytes);

        $response = handleValidatePlayer($request);

        return $response->serializeToString();
    }
);

echo "User gRPC server running on port 50052...\n";
$server->run();