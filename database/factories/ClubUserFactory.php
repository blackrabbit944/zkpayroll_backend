<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Club;
use App\Models\ClubUser;
use App\Models\DiscordUser;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClubUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClubUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $club = Club::factory()->create();
        $user = User::factory()->create();

        $discord_user = DiscordUser::factory()->create(['user_id'=>$user->user_id]);

        $type = $this->faker->randomElement(['unbind_discord','joined','left']);
        switch($type) {
            case 'unbind_discord':
                $join_time = 0;
                $left_time = 0;
                $token_number =  $this->faker->numberBetween(1, 8);
                break;
            case 'joined':
                $join_time = $this->faker->unixTime;
                $left_time = 0;
                $token_number =  $this->faker->numberBetween(1, 8);
                break;
            case 'left';
                $join_time = $this->faker->unixTime;
                $left_time = $this->faker->unixTime;
                $token_number =  0;
                break;
        }

        return [
            'contract_address'  =>  $club->contract_address,
            'wallet_address'    =>  $user->wallet_address,
            'token_number'      =>  $token_number,
            'status'            =>  'success',
            'join_time'         =>  $join_time,
            'left_time'         =>  $left_time,
            'create_time'   =>  $this->faker->unixTime,
            'update_time'   =>  null,
            'delete_time'   =>  null,
        ];
    }
}

