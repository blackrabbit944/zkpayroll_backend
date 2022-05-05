<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DiscordLog;
use App\Models\DiscordUser;

use App\Models\Club;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiscordLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscordLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $action_type = $this->faker->randomElement(['add_member','remove_member','send_message','send_admin_message']);
        
        $discord_user_id = null;
        $content  = null;

       

        switch($action_type) {
            case 'add_member':
            case 'remove_member':
                $user = User::factory()->create();
                $discord_user = DiscordUser::factory()->create(['user_id'=>$user->user_id]);
                $discord_user_id = $discord_user->discord_user_id;
                break;
            case 'send_message':
            case 'send_admin_message':
                $content = $this->faker->sentence;
        }
        


        return [
            'action_type'   =>  $action_type,
            'club_id'       =>  Club::factory(),
            'guild_id'      =>  $this->faker->unixTime,
            'discord_user_id' => $discord_user_id,
            'content'       =>  $content,
            'status'        =>  $this->faker->randomElement(['success','failed']),
            'create_time'   =>  $this->faker->unixTime,
            'update_time'   =>  null,
        ];
    }
}
