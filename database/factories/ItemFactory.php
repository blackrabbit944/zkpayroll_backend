<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Collection;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $collection = Collection::factory()->create();

        return [
            'contract_address'=>  $collection->contract_address,
            'token_id'        =>  1,
            'image_url'       =>  'ipfs://QmQnruDcVJpkFf3MvumGDMFUsB83QhXiyFKNMxVM5XHUe9/6711.png'
        ];
    }
}
