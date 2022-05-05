<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Club;
use App\Models\DraftContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DraftContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DraftContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'user_id'   =>  User::factory(),
            'content'   =>  $this->faker->paragraph
        ];
    }
}
