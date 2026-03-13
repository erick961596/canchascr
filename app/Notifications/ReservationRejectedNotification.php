<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationRejectedNotification extends Notification
{
    use Queueable;
    public function __construct(public Reservation $reservation) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $r = $this->reservation;
        return (new MailMessage)
            ->subject('Reserva no confirmada — ' . $r->court->name)
            ->greeting('Hola, ' . $notifiable->name)
            ->line("Lamentablemente tu reserva en **{$r->court->venue->name}** no pudo ser confirmada.")
            ->when($r->rejection_reason, fn($m) => $m->line("**Motivo:** {$r->rejection_reason}"))
            ->line('Podés intentar con otro horario disponible.')
            ->action('Explorar canchas', url('/player/explorar'));
    }
}
