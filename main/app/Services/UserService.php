<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllUsers()
    {
        // Load ranking relationship so we don't make N+1 queries
        return User::with('ranking')
                   ->select('id', 'name', 'email', 'score', 'created_at')
                   ->orderBy('score', 'desc')
                   ->get();
    }

    public function getUserById(int $id)
    {
        return User::with('ranking')
                   ->select('id', 'name', 'email', 'score', 'created_at')
                   ->find($id);
    }

    public function updateProfile(int $userId, array $data)
    {
        $user = User::findOrFail($userId);
        $user->update($data);

        return $user->fresh(); // fresh() re-fetches from DB so you get updated data
    }
}