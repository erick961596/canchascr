<!DOCTYPE html>
<html><body style="font-family:Inter,sans-serif;background:#f5f5f5;padding:24px">
<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden">
    <div style="background:#000;padding:24px;text-align:center"><h1 style="color:#fff;margin:0;font-size:22px;font-weight:800">SuperCancha</h1></div>
    <div style="padding:32px">
        <p>Notificación de reserva - {{ $reservation->court->name ?? '' }}</p>
        <p style="color:#666;font-size:13px">Fecha: {{ $reservation->reservation_date->format('d/m/Y') ?? '' }} · {{ $reservation->start_time ?? '' }} - {{ $reservation->end_time ?? '' }}</p>
        <p style="font-size:12px;color:#aaa;text-align:center">SuperCancha · Costa Rica</p>
    </div>
</div></body></html>
