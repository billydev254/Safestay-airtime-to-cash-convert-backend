<?php

namespace Database\Factories;

use App\Models\Conversion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversion>
 */
class ConversionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'airtime',
            'network' => 'safaricom',
            'sender_number' => '254712345678',
            'mpesa_number' => '254712345678',
            'amount_in' => 500,
            'cashback_pct' => 80,
            'amount_payout' => 400,
            'status' => 'awaiting_intake',
        ];
    }
}
