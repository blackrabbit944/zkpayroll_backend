<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DiscordUser;
// use App\Models\Club;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiscordUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscordUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'       =>  User::factory(),
            'discord_user_id'   =>  $this->faker->unixTime,
            'name'          =>  $this->faker->name,
            'avatar'        =>  Str::random(32),
            'email'         =>  $this->faker->email,
            'discriminator' =>  $this->faker->numberBetween(1000, 9000),
            'access_token'  =>  Str::random(32),
            'refresh_token' =>  Str::random(32),
            'token_expire_time'   =>   $this->faker->unixTime,
            'create_time'   =>  $this->faker->unixTime,
            'update_time'   =>  null,
            'delete_time'   =>  null
        ];
    }
}
