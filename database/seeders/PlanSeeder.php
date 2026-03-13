<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Básico',      'description' => 'Ideal para empezar.',         'price' => 15000,  'court_limit' => 2],
            ['name' => 'Profesional', 'description' => 'Para sedes en crecimiento.',  'price' => 35000,  'court_limit' => 6],
            ['name' => 'Empresarial', 'description' => 'Para grandes complejos.',     'price' => 75000,  'court_limit' => 20],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['name' => $plan['name']], array_merge($plan, ['active' => true]));
        }
    }
}
