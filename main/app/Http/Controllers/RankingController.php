<?php

namespace App\Http\Controllers;

use App\Services\RankingService;

class RankingController extends Controller
{
    public function __construct(protected RankingService $rankingService) {}

    // GET /api/ranking/leaderboard
    // Pulls from Redis cache, falls back to DB if cache empty
    public function leaderboard()
    {
        $leaderboard = $this->rankingService->getLeaderboard();

        return response()->json([
            'status' => 'success',
            'data'   => $leaderboard,
        ]);
    }

    // GET /api/ranking/me
    // Get current logged in player's rank
    public function myRank()
    {
        $userId = auth('api')->id();
        $rank   = $this->rankingService->getPlayerRank($userId);

        return response()->json([
            'status' => 'success',
            'data'   => $rank,
        ]);
    }
}