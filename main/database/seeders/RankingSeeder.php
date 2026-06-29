<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ranking;
use Illuminate\Database\Seeder;

class RankingSeeder extends Seeder
{
    public function run(): void
    {
        // Give existing users some wins/losses for realistic leaderboard
        $rankData = [
            'player1@game.com' => ['wins' => 10, 'losses' => 2,  'points' => 100],
            'player2@game.com' => ['wins' => 7,  'losses' => 5,  'points' => 80],
            'player3@game.com' => ['wins' => 5,  'losses' => 6,  'points' => 60],
            'player4@game.com' => ['wins' => 3,  'losses' => 8,  'points' => 40],
        ];

        foreach ($rankData as $email => $stats) {
            $user = User::where('email', $email)->first();

            if ($user) {
                Ranking::where('user_id', $user->id)->update($stats);
            }
        }
    }
}