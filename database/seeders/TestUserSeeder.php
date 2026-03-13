<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Owners de prueba
        $owners = [
            ['name' => 'Carlos Mora', 'email' => 'owner1@test.com'],
            ['name' => 'Daniela Arias', 'email' => 'owner2@test.com'],
            ['name' => 'Rodrigo Salas', 'email' => 'owner3@test.com'],
        ];

        foreach ($owners as $data) {
            User::firstOrCreate(['email' => $data['email']], [
                'name'     => $data['name'],
                'password' => Hash::make('password'),
                'role'     => 'owner',
                'phone'    => '8' . rand(100,999) . '-' . rand(1000,9999),
            ]);
        }

        // Players de prueba
        $players = [
            ['name' => 'Andrea Jiménez', 'email' => 'player1@test.com'],
            ['name' => 'Diego Vargas',    'email' => 'player2@test.com'],
            ['name' => 'Sofía Castro',    'email' => 'player3@test.com'],
            ['name' => 'Esteban Rojas',   'email' => 'player4@test.com'],
            ['name' => 'Laura Blanco',    'email' => 'player5@test.com'],
        ];

        foreach ($players as $data) {
            User::firstOrCreate(['email' => $data['email']], [
                'name'     => $data['name'],
                'password' => Hash::make('password'),
                'role'     => 'user',
                'phone'    => '7' . rand(100,999) . '-' . rand(1000,9999),
            ]);
        }

        $this->command->info('✓ Usuarios de prueba creados (owner1@test.com, player1@test.com — password: password)');
    }
}
