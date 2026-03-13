<?php

namespace Database\Seeders;

use App\Models\{User, Court, Reservation, Subscription, SubscriptionPayment, Plan};
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestReservationSeeder extends Seeder
{
    public function run(): void
    {
        $players = User::where('role', 'user')->get();
        $courts  = Court::where('active', true)->with('venue')->get();
        $plans   = Plan::where('active', true)->get();

        if ($players->isEmpty() || $courts->isEmpty()) {
            $this->command->warn('⚠ Corré primero TestUserSeeder y TestVenueSeeder.');
            return;
        }

        $statuses = ['confirmed','confirmed','confirmed','pending','cancelled'];

        // Crear 25 reservas distribuidas en los últimos 30 días y próximos 14 días
        for ($i = 0; $i < 25; $i++) {
            $court  = $courts->random();
            $player = $players->random();
            $date   = Carbon::today()->addDays(rand(-20, 14));
            $hour   = rand(7, 20);
            $start  = sprintf('%02d:00:00', $hour);
            $end    = sprintf('%02d:00:00', $hour + 1);
            $status = $statuses[array_rand($statuses)];

            // Avoid duplicate slot
            $exists = Reservation::where('court_id', $court->id)
                ->where('reservation_date', $date->toDateString())
                ->where('start_time', $start)
                ->whereIn('status', ['pending','confirmed'])
                ->exists();

            if ($exists) continue;

            Reservation::create([
                'court_id'         => $court->id,
                'user_id'          => $player->id,
                'reservation_date' => $date->toDateString(),
                'start_time'       => $start,
                'end_time'         => $end,
                'total_price'      => $court->price_per_hour,
                'status'           => $status,
                'payment_status'   => $status === 'confirmed' ? 'verified' : ($status === 'pending' ? 'pending' : 'pending'),
                'sinpe_reference'  => $status !== 'cancelled' ? 'REF' . rand(100000, 999999) : null,
                'notes'            => rand(0,1) ? 'Reserva para partido amistoso' : null,
            ]);
        }

        // Suscripciones en distintos estados para el admin
        $owners = User::where('role', 'owner')->get();
        foreach ($owners as $i => $owner) {
            // Add a pending manual subscription payment to test admin approval
            $sub = Subscription::where('user_id', $owner->id)->first();
            if ($sub && $i === 0) {
                // One pending SINPE payment
                $pendingSub = Subscription::create([
                    'user_id'        => $owner->id,
                    'plan_id'        => $plans->last()->id,
                    'status'         => 'pending',
                    'payment_method' => 'manual',
                    'price'          => $plans->last()->price,
                    'starts_at'      => null,
                    'ends_at'        => null,
                ]);
                SubscriptionPayment::create([
                    'subscription_id' => $pendingSub->id,
                    'amount'          => $plans->last()->price,
                    'method'          => 'manual',
                    'status'          => 'pending',
                ]);
            }
        }

        $this->command->info('✓ Reservas y suscripciones de prueba creadas.');
        $this->command->info('  → Revisá /admin/pagos-pendientes para aprobar una suscripción SINPE.');
    }
}
