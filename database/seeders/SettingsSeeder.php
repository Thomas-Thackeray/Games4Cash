<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'pricing_discount_percent' => '85',
            'usd_to_gbp_rate'          => '1.36',
            'age_reduction_per_year'   => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        }

        $this->command->info('Default settings seeded.');
    }
}
