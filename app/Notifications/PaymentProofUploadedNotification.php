<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentProofUploadedNotification extends Notification
{
    use Queueable;
    public function __construct(public Reservation $reservation) {}
    public function via($notifiable): array { return ['mail']; }
    public function toMail($notifiable): MailMessage
    {
        $r = $this->reservation;
        return (new MailMessage)
            ->subject('Nuevo comprobante de pago — ' . $r->user->name)
            ->greeting('Hola, ' . $notifiable->name)
            ->line("**{$r->user->name}** subió un comprobante para su reserva en **{$r->court->name}**.")
            ->line("**Fecha:** " . $r->reservation_date->format('d/m/Y') . " | {$r->start_time} — {$r->end_time}")
            ->line("**Total:** ₡" . number_format($r->total_price, 0, ',', '.'))
            ->action('Revisar reserva', url("/owner/reservas"))
            ->line('Verificá el pago y confirmá o rechazá la reserva.');
    }
}
