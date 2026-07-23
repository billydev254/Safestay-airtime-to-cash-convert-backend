<?php

namespace Database\Factories;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category' => 'data',
            'label' => '1GB',
            'price' => $this->faker->numberBetween(10, 1000),
            'validity_text' => 'Valid 24 Hours',
            'active' => true,
            'sort_order' => 0,
        ];
    }
}
