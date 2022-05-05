<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Link::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {


        return [
            'user_id'         =>  User::factory(),
            'sort_id'         =>  0,
            'url'             =>  $this->faker->url,
            'link_type'       =>  $this->faker->randomElement(config('link.allow_types')),
            'show_type'       =>  $this->faker->randomElement(['icon','button','text']),
            'text'            =>  $this->faker->name,
            'is_hidden'       =>  0,
        ];



    }
}
