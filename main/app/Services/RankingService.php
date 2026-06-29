<?php

namespace App\Services;

use App\Models\Ranking;
use Illuminate\Support\Facades\Redis;

class RankingService
{
    private const CACHE_KEY = 'ranking:leaderboard';
    private const CACHE_TTL = 300; // 5 minutes in seconds

    public function getLeaderboard(): array
    {
        // Step 1 — check Redis first (cache-aside pattern)
        $cached = Redis::get(self::CACHE_KEY);

        if ($cached) {
            return [
                'source' => 'cache',   // so you can see in response it came from Redis
                'data'   => json_decode($cached, true),
            ];
        }

        // Step 2 — cache miss, hit the database
        $leaderboard = Ranking::with('user:id,name')
                              ->orderBy('points', 'desc')
                              ->take(50)         // top 50 players only
                              ->get()
                              ->map(function ($ranking, $index) {
                                  return [
                                      'rank'     => $index + 1,
                                      'player'   => $ranking->user->name,
                                      'user_id'  => $ranking->user_id,
                                      'points'   => $ranking->points,
                                      'wins'     => $ranking->wins,
                                      'losses'   => $ranking->losses,
                                      'win_rate' => $ranking->win_rate, // accessor from model
                                  ];
                              })->toArray();

        // Step 3 — store in Redis so next request hits cache
        Redis::setex(self::CACHE_KEY, self::CACHE_TTL, json_encode($leaderboard));

        return [
            'source' => 'database',
            'data'   => $leaderboard,
        ];
    }

    public function getPlayerRank(int $userId): array
    {
        // Individual rank — check Redis sorted set first
        $cachedRank = Redis::zscore('ranking:scores', $userId);

        if ($cachedRank !== null) {
            // zrevrank gives position (0-indexed), +1 to make it 1-indexed
            $position = Redis::zrevrank('ranking:scores', $userId);

            return [
                'source'   => 'cache',
                'rank'     => $position + 1,
                'points'   => $cachedRank,
                'user_id'  => $userId,
            ];
        }

        // Not in sorted set — pull from DB and cache it
        $ranking = Ranking::where('user_id', $userId)
                          ->with('user:id,name')
                          ->firstOrFail();

        // zadd adds to sorted set with score = points
        Redis::zadd('ranking:scores', $ranking->points, $userId);

        // Count how many players have more points = rank position
        $rank = Ranking::where('points', '>', $ranking->points)->count() + 1;

        return [
            'source'   => 'database',
            'rank'     => $rank,
            'points'   => $ranking->points,
            'wins'     => $ranking->wins,
            'losses'   => $ranking->losses,
            'win_rate' => $ranking->win_rate,
            'player'   => $ranking->user->name,
        ];
    }

    // Called externally when a match finishes to keep cache fresh
    public function bustCache(int $userId, int $newPoints): void
    {
        // Update sorted set score
        Redis::zadd('ranking:scores', $newPoints, $userId);

        // Delete leaderboard cache so it rebuilds on next request
        Redis::del(self::CACHE_KEY);
    }
}