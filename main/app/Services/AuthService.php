<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ranking;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'score'    => 0,
        ]);

        Ranking::create([
            'user_id' => $user->id,
            'wins'    => 0,
            'losses'  => 0,
            'points'  => 0,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials): array|null
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return null;
        }

        return [
            'user'       => auth('api')->user(),
            'token'      => $token,
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }

    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function refresh(): array
    {
        $newToken = JWTAuth::refresh(JWTAuth::getToken());

        return [
            'token'      => $newToken,
            'expires_in' => config('jwt.ttl') * 60,
        ];
    }
}