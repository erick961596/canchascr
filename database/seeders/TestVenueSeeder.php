<?php

namespace Database\Seeders;

use App\Models\{User, Plan, Subscription, Venue, Court, Schedule};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestVenueSeeder extends Seeder
{
    public function run(): void
    {
        $owners = User::where('role', 'owner')->get();
        $plans  = Plan::where('active', true)->get();

        if ($owners->isEmpty() || $plans->isEmpty()) {
            $this->command->warn('⚠ Corré primero TestUserSeeder y PlanSeeder.');
            return;
        }

        // Activa suscripciones para cada owner
        foreach ($owners as $owner) {
            if (!$owner->subscriptions()->where('status','active')->exists()) {
                Subscription::create([
                    'user_id'        => $owner->id,
                    'plan_id'        => $plans->random()->id,
                    'status'         => 'active',
                    'payment_method' => 'manual',
                    'price'          => $plans->first()->price,
                    'starts_at'      => now()->subDays(rand(5,30)),
                    'ends_at'        => now()->addDays(rand(10,25)),
                    'last_payment_at'=> now()->subDays(rand(1,10)),
                ]);
            }
        }

        $venuesData = [
            [
                'owner'     => $owners[0],
                'name'      => 'Complejo Deportivo La Sabana',
                'province'  => 'San José',
                'canton'    => 'San José',
                'district'  => 'Mata Redonda',
                'address'   => 'Contiguo al Parque La Sabana, San José',
                'phone'     => '2220-1111',
                'lat'       => 9.9340,
                'lng'       => -84.1088,
                'amenities' => ['Parqueo', 'Vestuarios', 'Cafetería', 'WiFi'],
                'courts' => [
                    ['name'=>'Cancha de Fútbol A', 'sport'=>'futbol',     'price'=>20000, 'features'=>['Césped sintético','Iluminación nocturna']],
                    ['name'=>'Cancha de Fútbol B', 'sport'=>'futbol',     'price'=>18000, 'features'=>['Césped sintético']],
                    ['name'=>'Cancha de Tenis 1',  'sport'=>'tenis',      'price'=>12000, 'features'=>['Piso duro','Iluminación']],
                ],
            ],
            [
                'owner'     => $owners[1],
                'name'      => 'SportCenter Escazú',
                'province'  => 'San José',
                'canton'    => 'Escazú',
                'district'  => 'San Rafael',
                'address'   => '200m norte del Mall Multiplaza, Escazú',
                'phone'     => '2288-4444',
                'lat'       => 9.9181,
                'lng'       => -84.1411,
                'amenities' => ['Parqueo', 'Vestuarios', 'Tienda deportiva'],
                'courts' => [
                    ['name'=>'Cancha Pádel 1',    'sport'=>'padel',      'price'=>15000, 'features'=>['Cristal panorámico','Iluminación LED']],
                    ['name'=>'Cancha Pádel 2',    'sport'=>'padel',      'price'=>15000, 'features'=>['Cristal panorámico']],
                    ['name'=>'Cancha Volleyball', 'sport'=>'volleyball', 'price'=>10000, 'features'=>['Piso de madera']],
                ],
            ],
            [
                'owner'     => $owners[2],
                'name'      => 'Arena Deportiva Heredia',
                'province'  => 'Heredia',
                'canton'    => 'Heredia',
                'district'  => 'Mercedes',
                'address'   => 'Del ICE 300m al oeste, Heredia centro',
                'phone'     => '2261-9999',
                'lat'       => 9.9991,
                'lng'       => -84.1205,
                'amenities' => ['Parqueo', 'Cafetería', 'Duchas'],
                'courts' => [
                    ['name'=>'Cancha Fútbol 5',       'sport'=>'futbol',     'price'=>16000, 'features'=>['Grama artificial']],
                    ['name'=>'Cancha Baloncesto',      'sport'=>'basquetbol', 'price'=>8000,  'features'=>['Tableros electrónicos']],
                ],
            ],
        ];

        $days = ['mon','tue','wed','thu','fri','sat','sun'];

        foreach ($venuesData as $vData) {
            $venue = Venue::firstOrCreate(
                ['slug' => Str::slug($vData['name'])],
                [
                    'owner_id'   => $vData['owner']->id,
                    'name'       => $vData['name'],
                    'province'   => $vData['province'],
                    'canton'     => $vData['canton'],
                    'district'   => $vData['district'],
                    'address'    => $vData['address'],
                    'phone'      => $vData['phone'],
                    'lat'        => $vData['lat'],
                    'lng'        => $vData['lng'],
                    'amenities'  => $vData['amenities'],
                    'active'     => true,
                ]
            );

            foreach ($vData['courts'] as $cData) {
                $court = Court::firstOrCreate(
                    ['venue_id' => $venue->id, 'name' => $cData['name']],
                    [
                        'sport'          => $cData['sport'],
                        'price_per_hour' => $cData['price'],
                        'slot_duration'  => 60,
                        'features'       => $cData['features'],
                        'active'         => true,
                    ]
                );

                // Create weekly schedule Mon-Sun 07:00-22:00
                foreach ($days as $day) {
                    Schedule::firstOrCreate(
                        ['court_id' => $court->id, 'day_of_week' => $day],
                        ['open_time' => '07:00', 'close_time' => '22:00', 'active' => true]
                    );
                }
            }
        }

        $this->command->info('✓ Sedes, canchas y horarios de prueba creados.');
    }
}
