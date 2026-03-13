<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('location_data')->count() > 0) return;

        $jsonPath = database_path('data.json');
        if (!file_exists($jsonPath)) {
            $this->command->warn('data.json not found at database/data.json. Skipping location seed.');
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $rows = [];

        foreach ($data as $province => $cantons) {
            foreach ($cantons as $canton => $districts) {
                foreach ($districts as $district) {
                    $rows[] = [
                        'province' => $province,
                        'canton'   => $canton,
                        'district' => $district,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('location_data')->insert($chunk);
        }

        $this->command->info('Loaded ' . count($rows) . ' locations from data.json');
    }
}
