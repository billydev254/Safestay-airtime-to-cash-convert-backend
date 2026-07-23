<?php

namespace Database\Factories;

use App\Models\Bundle;
use App\Models\BundleOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BundleOrder>
 */
class BundleOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bundle_id' => Bundle::factory(),
            'recipient_number' => '254712345678',
            'mpesa_number' => '254798765432',
            'amount' => 20,
            'status' => 'pending_payment',
        ];
    }
}
