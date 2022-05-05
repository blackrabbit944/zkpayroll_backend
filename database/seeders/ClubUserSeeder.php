<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ClubUser;
use App\Models\Club;

class ClubUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $club = Club::factory()->create();

        ClubUser::factory()
            ->count(50)
            ->create([
                'contract_address'  =>  $club->contract_address
            ]);

    }
}
