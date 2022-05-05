<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ClubUserSeeder;
use Database\Seeders\DiscordLogSeeder;
use Database\Seeders\TxSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ClubUserSeeder::class,
            DiscordLogSeeder::class,
            TxSeeder::class,
        ]);
    }
}
