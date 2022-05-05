<?php

namespace Database\Factories;

use App\Models\Tx;
use App\Models\DiscordUser;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

class TxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tx::class;

    /**
     * 表示拥有一个from_user和from_user对应的discord_user数据
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function hasUser()
    {
        
        $address = '0x'.Str::random(40);

        $user = User::factory()->create(['wallet_address'=>$address]);
        $discord_user = DiscordUser::factory()->create(['user_id'=>$user->user_id]);
        return $address;
    }



    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'chain' =>  'eth',
            'contract_address'  =>  '0x'.Str::random(40),
            'tx_type'           =>  $this->faker->randomElement(['mint', 'transfer']),
            'token_id'          =>  $this->faker->numberBetween(1, 1000),
            'from_address'      =>  $this->hasUser(),
            'to_address'        =>  $this->hasUser(),
            'tx_hash'           =>  '0x'.Str::random(64),
            'tx_index_id'       =>  0,
            'price'             =>  0,
            'royalty'           =>  0,
            'action_time'   =>   $this->faker->unixTime,
            'create_time'   =>  $this->faker->unixTime,
        ];
    }
}
