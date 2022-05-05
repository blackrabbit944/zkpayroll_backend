<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Tx;
use App\Models\Club;

class TxSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $club_id = 3;

        $club = Club::find($club_id);

        Tx::factory()
            ->count(200)
            ->create([
                'contract_address'   => $club->contract_address,
            ]);

    }
}
