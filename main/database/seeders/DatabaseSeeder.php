<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters — users must exist before rankings
        $this->call([
            UserSeeder::class,
            RankingSeeder::class,
        ]);
    }
}