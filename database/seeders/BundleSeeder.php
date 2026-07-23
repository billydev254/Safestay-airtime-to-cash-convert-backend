<?php

namespace Database\Seeders;

use App\Models\Bundle;
use Illuminate\Database\Seeder;

/**
 * Mirrors the current catalog in the approved mockup
 * (airtimeswap-mockup/src/lib/data.ts) exactly, so the real app starts
 * showing the same prices the client already confirmed.
 */
class BundleSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'data' => [
                ['label' => '250MB', 'price' => 20, 'validity_text' => 'Valid 24 Hours'],
                ['label' => '1GB', 'price' => 19, 'validity_text' => 'Valid 1 Hour'],
                ['label' => '350MB', 'price' => 49, 'validity_text' => 'Valid 7 Days'],
                ['label' => '1.5GB', 'price' => 50, 'validity_text' => 'Valid 3 Hours'],
                ['label' => '1.25GB', 'price' => 55, 'validity_text' => 'Valid till Midnight'],
                ['label' => '1GB', 'price' => 100, 'validity_text' => 'Valid 24 Hours'],
                ['label' => '2.5GB', 'price' => 300, 'validity_text' => 'Valid 7 Days'],
                ['label' => '6GB', 'price' => 700, 'validity_text' => 'Valid 7 Days'],
            ],
            'minutes' => [
                ['label' => '43 Minutes', 'price' => 22, 'validity_text' => 'Valid 3 Hours'],
                ['label' => '50 Minutes', 'price' => 51, 'validity_text' => 'Valid till Midnight'],
                ['label' => '100 Minutes', 'price' => 118, 'validity_text' => 'Valid till Midnight'],
                ['label' => '200 Minutes', 'price' => 265, 'validity_text' => 'Valid 7 Days'],
                ['label' => '400 Minutes', 'price' => 500, 'validity_text' => 'Valid 30 Days'],
            ],
            'sms' => [
                ['label' => '20 SMS', 'price' => 5, 'validity_text' => 'Valid 24 Hours'],
                ['label' => '200 SMS', 'price' => 10, 'validity_text' => 'Valid 24 Hours'],
                ['label' => '1000 SMS', 'price' => 30, 'validity_text' => 'Valid 7 Days'],
            ],
        ];

        foreach ($catalog as $category => $bundles) {
            foreach ($bundles as $sortOrder => $bundle) {
                Bundle::updateOrCreate(
                    ['category' => $category, 'label' => $bundle['label'], 'price' => $bundle['price']],
                    [...$bundle, 'category' => $category, 'sort_order' => $sortOrder, 'active' => true]
                );
            }
        }
    }
}
