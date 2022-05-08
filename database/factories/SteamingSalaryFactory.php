<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Salary;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalaryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Salary::class;

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
            'chain'         =>  'eth',
            'contract_address'  =>  '0x'.Str::random(40),
            'amount'        =>  $faker->numberBetween(1000,20000),
        ];
    }
}
