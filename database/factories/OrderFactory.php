<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Collection;
use App\Models\Item;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'owner_address'    => $user->wallet_address
        ]);

        return [
            'chain'           => 'eth',
            'contract_address'  =>  $item->contract_address,
            'token_id'          =>  $item->token_id,
            'from_address'      =>  $user->wallet_address,
            'price'             =>  0.99,
            'expire_time'       => time() + 86400
        ];
    }
}
