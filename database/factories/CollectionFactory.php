<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Item;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CollectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Collection::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $user = User::factory()->make();
        return [
            'name'              =>  $this->faker->name(),
            'symbol'            =>  $this->faker->name(),
            'contract_address'  =>  '0x'.Str::random(40),
        ];
    }
}
