<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request)
    {
        $courtIds = auth()->user()->venues()
            ->with('courts')
            ->get()
            ->flatMap(fn($v) => $v->courts->pluck('id'));

        $query = Reservation::with(['court.venue', 'user'])
            ->whereIn('court_id', $courtIds)
            ->latest();

        if ($request->status) $query->where('status', $request->status);
        if ($request->date)   $query->whereDate('reservation_date', $request->date);

        $reservations = $query->paginate(15)->withQueryString();

        return view('pages.owner.reservations.index', compact('reservations'));
    }

    public function confirm(Reservation $reservation)
    {
        $this->authorizeOwner($reservation);

        $reservation->update([
            'status'         => 'confirmed',
            'payment_status' => 'verified',
        ]);

        $this->notifications->reservationConfirmed($reservation);

        return response()->json(['message' => 'Reserva confirmada.']);
    }

    public function reject(Request $request, Reservation $reservation)
    {
        $this->authorizeOwner($reservation);

        $reservation->update([
            'status'         => 'cancelled',
            'payment_status' => 'rejected',
        ]);

        $this->notifications->reservationCancelled($reservation);

        return response()->json(['message' => 'Reserva rechazada.']);
    }

    public function calendarData(Request $request)
    {
        $courtIds = auth()->user()->venues()
            ->with('courts')
            ->get()
            ->flatMap(fn($v) => $v->courts->pluck('id'));

        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end   = $request->input('end', now()->endOfMonth()->toDateString());

        $reservations = Reservation::with(['court', 'user'])
            ->whereIn('court_id', $courtIds)
            ->whereBetween('reservation_date', [$start, $end])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->map(fn($r) => [
                'id'    => $r->id,
                'title' => $r->court->name . ' - ' . $r->user->name,
                'start' => $r->reservation_date->format('Y-m-d') . 'T' . $r->start_time,
                'end'   => $r->reservation_date->format('Y-m-d') . 'T' . $r->end_time,
                'color' => $r->status === 'confirmed' ? '#17c653' : '#f6c000',
                'extendedProps' => [
                    'status'       => $r->status,
                    'user'         => $r->user->name,
                    'payment_status' => $r->payment_status,
                    'proof_url'    => $r->proof_url,
                ],
            ]);

        return response()->json($reservations);
    }

    private function authorizeOwner(Reservation $reservation): void
    {
        $ownerId = $reservation->court->venue->owner_id;
        if ($ownerId !== auth()->id()) abort(403);
    }
}
