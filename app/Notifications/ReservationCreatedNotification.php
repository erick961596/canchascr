<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $r = $this->reservation;
        return (new MailMessage)
            ->subject('Reserva creada — ' . $r->court->name)
            ->greeting('Hola, ' . $notifiable->name . '!')
            ->line("Tu reserva en **{$r->court->venue->name}** fue recibida.")
            ->line("**Cancha:** {$r->court->name}")
            ->line("**Fecha:** " . $r->reservation_date->format('d/m/Y'))
            ->line("**Horario:** {$r->start_time} — {$r->end_time}")
            ->line("**Total:** ₡" . number_format($r->total_price, 0, ',', '.'))
            ->line('Para confirmarla, subí el comprobante de pago por SINPE.')
            ->action('Ver reserva', url("/player/reservas/{$r->id}"))
            ->line('Gracias por usar SuperCancha!');
    }
}
