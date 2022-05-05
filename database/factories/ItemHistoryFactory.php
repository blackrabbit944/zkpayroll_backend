<?php

namespace Database\Factories;

use App\Models\ItemHistory;
use App\Models\User;
use App\Models\Item;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $item = Item::factory()->create([
            'owner_address'    => $user->wallet_address
        ]);

        return [
            'chain'             => 'eth',
            'contract_address'  =>  $item->contract_address,
            'token_id'          =>  $item->token_id,
            'action_name'       =>  $this->faker->randomElement(['sale', 'transfer']),

            'tx_hash'           =>  '0x'.Str::random(62),

            'from_address'      =>  $user->wallet_address,
            'to_address'        =>  $user2->wallet_address,
            'price'             =>  0.99,
            'action_time'       => time(),
            'log_index'         =>  0
        ];
    }
}
