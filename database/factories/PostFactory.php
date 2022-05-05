<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Club;
use App\Models\Post;
use App\Models\DraftContent;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'user_id'   =>  User::factory(),
            'club_id'   =>  Club::factory(),
            'title'     =>  $this->faker->title,
            'draft_content_id'  =>  DraftContent::factory(),
        ];
    }
}
