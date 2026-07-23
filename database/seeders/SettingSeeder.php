<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('safaricom_cashback_pct', '80');
        Setting::set('airtel_cashback_pct', '50');
        Setting::set('bonga_rate', '50');
    }
}
