<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::create([
            'latitude' => '-6.200000', // Default Jakarta latitude
            'longitude' => '106.816666', // Default Jakarta longitude
            'radius' => 100, // 100 meters
        ]);
    }
}
