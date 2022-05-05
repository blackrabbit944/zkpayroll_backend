<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Club;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClubFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Club::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'       =>  User::factory(),
            'name'          =>  $this->faker->name,
            'introduction'  =>  $this->faker->sentence(),
            'contract_address'  =>  '0x'.Str::random(40),
            'unique_name'   =>  $this->faker->name.$this->faker->randomDigit,
            'unique_hash'   =>  bin2hex(random_bytes(32)),
            'nft_bg'        =>  'orange',
            'nft_font'      =>  7,
            'name_in_nft'   =>  Str::random(8),
            'passcard_max_count'             =>  1000,
            'passcard_count'                 =>  0,
            'passcard_holding_user_count'    =>  0,
            'passcard_type'                  =>  'default',
        ];
    }
}
