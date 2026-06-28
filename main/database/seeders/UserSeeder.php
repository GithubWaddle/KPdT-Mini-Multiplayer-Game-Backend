<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();

        // Or manually:
        User::create([
            'name'     => 'Player One',
            'email'    => 'player1@game.com',
            'password' => bcrypt('password123'),
            'score'    => 100,
        ]);
    }
}
