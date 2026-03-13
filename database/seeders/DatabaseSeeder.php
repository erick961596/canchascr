<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,    // Provincias, cantones, distritos de CR
            PlanSeeder::class,        // 3 planes: Básico, Profesional, Empresarial
            AdminSeeder::class,       // admin@supercancha.com / admin123
        ]);

        // Solo correr en desarrollo - datos falsos para testing
        if (app()->environment('local', 'development')) {
            $this->call([
                TestUserSeeder::class,        // owners + players de prueba
                TestVenueSeeder::class,       // sedes, canchas, horarios
                TestReservationSeeder::class, // reservas + suscripciones pendientes
            ]);
        }
    }
}
