<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DiscordGuild;
use App\Models\Club;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiscordGuildFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscordGuild::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'club_id'       =>  Club::factory(),
            'guild_id'      =>  $this->faker->unixTime,
            'discord_user_id'   =>  $this->faker->unixTime,
            'name'          =>  $this->faker->name,
            'webhook_id'    =>  null,
            'webhook_token' =>  null,
            'admin_webhook_token'   =>  null,
            'admin_webhook_id'  =>  null,
            'create_time'   =>  $this->faker->unixTime,
            'update_time'   =>  null
        ];
    }
}
