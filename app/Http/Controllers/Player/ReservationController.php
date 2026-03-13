<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    public function __construct(
        private AvailabilityService $availability,
        private NotificationService $notifications
    ) {}

    public function index()
    {
        $reservations = Reservation::with(['court.venue'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('pages.player.bookings.index', compact('reservations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'court_id'         => 'required|uuid|exists:courts,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time'       => 'required',
            'end_time'         => 'required',
            'notes'            => 'nullable|string|max:500',
        ]);

        $court = Court::findOrFail($data['court_id']);

        // Verify slot is still available
        $slots = $this->availability->getSlots($court, $data['reservation_date']);
        $slotOk = collect($slots)->first(fn($s) =>
            $s['start'] === $data['start_time'] && $s['end'] === $data['end_time'] && $s['available']
        );

        if (!$slotOk) {
            return response()->json(['message' => 'El horario ya no está disponible.'], 422);
        }

        $hours  = (strtotime($data['end_time']) - strtotime($data['start_time'])) / 3600;
        $price  = $court->price_per_hour * $hours;

        $reservation = Reservation::create([
            'court_id'         => $court->id,
            'user_id'          => auth()->id(),
            'reservation_date' => $data['reservation_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'total_price'      => $price,
            'status'           => 'pending',
            'payment_status'   => 'pending',
            'notes'            => $data['notes'] ?? null,
        ]);

        $this->notifications->reservationCreated($reservation);

        return response()->json([
            'message'        => '¡Reserva creada! Subí el comprobante de pago.',
            'reservation_id' => $reservation->id,
        ]);
    }

    public function uploadProof(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('proof')->store(
            'reservations/proofs/' . $reservation->id,
            's3'
        );

        $reservation->update([
            'payment_proof'   => $path,
            'payment_status'  => 'pending',
        ]);

        return response()->json([
            'message'   => 'Comprobante subido. El dueño lo verificará pronto.',
            'proof_url' => Storage::disk('s3')->url($path),
        ]);
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        if (!in_array($reservation->status, ['pending'])) {
            return response()->json(['message' => 'No podés cancelar esta reserva.'], 422);
        }

        $reservation->update(['status' => 'cancelled']);
        $this->notifications->reservationCancelled($reservation);

        return response()->json(['message' => 'Reserva cancelada.']);
    }
}
