<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationConfirmedNotification extends Notification
{
    use Queueable;
    public function __construct(public Reservation $reservation) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $r = $this->reservation;
        return (new MailMessage)
            ->subject('¡Reserva confirmada! — ' . $r->court->name)
            ->greeting('¡Todo listo, ' . $notifiable->name . '!')
            ->line("Tu reserva en **{$r->court->venue->name}** fue **confirmada**.")
            ->line("**Cancha:** {$r->court->name}")
            ->line("**Fecha:** " . $r->reservation_date->format('d/m/Y'))
            ->line("**Horario:** {$r->start_time} — {$r->end_time}")
            ->line("**Dirección:** {$r->court->venue->address}")
            ->action('Ver mi reserva', url("/player/reservas/{$r->id}"))
            ->line('¡Que disfrutes el juego!');
    }
}
