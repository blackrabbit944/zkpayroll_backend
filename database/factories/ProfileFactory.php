<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Profile;
use App\Models\DraftContent;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

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
            'bio'           =>  $this->faker->sentence(),
            'ens'           =>  $this->faker->word.'.eth',
        ];
    }
}
