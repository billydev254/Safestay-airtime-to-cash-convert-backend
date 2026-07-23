<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin credentials come from .env so nothing real is ever
        // hardcoded/committed — set ADMIN_EMAIL/ADMIN_PASSWORD there before
        // seeding for a real deploy.
        User::factory()->create([
            'name' => 'Admin',
            'email' => env('ADMIN_EMAIL', 'admin@safestay.co.ke'),
            'password' => bcrypt(env('ADMIN_PASSWORD', 'change-me-immediately')),
        ]);

        $this->call([
            SettingSeeder::class,
            BundleSeeder::class,
        ]);
    }
}
