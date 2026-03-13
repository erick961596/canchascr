<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reservation;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function reservationCreated(Reservation $reservation): void
    {
        $user  = $reservation->user;
        $owner = $reservation->court->venue->owner;

        $this->sendMail($user,  'reservation.created-user',  ['reservation' => $reservation]);
        $this->sendMail($owner, 'reservation.created-owner', ['reservation' => $reservation]);
    }

    public function reservationConfirmed(Reservation $reservation): void
    {
        $this->sendMail(
            $reservation->user,
            'reservation.confirmed',
            ['reservation' => $reservation]
        );
    }

    public function reservationCancelled(Reservation $reservation): void
    {
        $this->sendMail(
            $reservation->user,
            'reservation.cancelled',
            ['reservation' => $reservation]
        );
    }

    public function subscriptionActivated(User $user): void
    {
        $this->sendMail($user, 'subscription.activated', ['user' => $user]);
    }

    private function sendMail(User $user, string $template, array $data): void
    {
        try {
            Mail::send("emails.{$template}", $data, function ($m) use ($user, $template) {
                $subject = $this->getSubject($template);
                $m->to($user->email, $user->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error("NotificationService mail error: {$e->getMessage()}", ['template' => $template]);
        }
    }

    private function getSubject(string $template): string
    {
        return match($template) {
            'reservation.created-user'  => '¡Reserva recibida! - SuperCancha',
            'reservation.created-owner' => 'Nueva reserva en tu cancha - SuperCancha',
            'reservation.confirmed'     => '¡Tu reserva fue confirmada! - SuperCancha',
            'reservation.cancelled'     => 'Tu reserva fue cancelada - SuperCancha',
            'subscription.activated'    => '¡Tu suscripción está activa! - SuperCancha',
            default                     => 'Notificación - SuperCancha',
        };
    }
}
