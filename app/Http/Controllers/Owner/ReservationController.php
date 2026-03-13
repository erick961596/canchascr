<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Reservation, Court, Venue};
use App\Services\{AvailabilityService, NotificationService};
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        private NotificationService  $notifications,
        private AvailabilityService  $availability
    ) {}

    public function index(Request $request)
    {
        $courtIds = auth()->user()->venues()
            ->with('courts')
            ->get()
            ->flatMap(fn($v) => $v->courts->pluck('id'));

        $query = Reservation::with(['court.venue', 'user', 'services.extraService'])
            ->whereIn('court_id', $courtIds)
            ->latest();

        if ($request->status) $query->where('status', $request->status);
        if ($request->date)   $query->whereDate('reservation_date', $request->date);
        if ($request->court)  $query->where('court_id', $request->court);

        $reservations = $query->paginate(20)->withQueryString();
        $venues = auth()->user()->venues()->with('courts')->get();

        return view('pages.owner.reservations.index', compact('reservations', 'venues'));
    }

    public function storeManual(Request $request)
    {
        $data = $request->validate([
            'court_id'         => 'required|uuid|exists:courts,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'client_name'      => 'required|string|max:150',
            'client_phone'     => 'nullable|string|max:30',
            'payment_status'   => 'required|in:pending,verified',
            'notes'            => 'nullable|string|max:500',
        ]);

        $court = Court::findOrFail($data['court_id']);
        if ($court->venue->owner_id !== auth()->id()) abort(403);

        $overlap = Reservation::where('court_id', $court->id)
            ->where('reservation_date', $data['reservation_date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(fn($q) =>
                $q->where(fn($q2) =>
                    $q2->where('start_time', '<', $data['end_time'])
                       ->where('end_time', '>', $data['start_time'])
                )
            )->exists();

        if ($overlap) {
            return response()->json(['message' => 'Ya existe una reserva en ese horario.'], 422);
        }

        $hours = (strtotime($data['end_time']) - strtotime($data['start_time'])) / 3600;
        $price = $court->price_per_hour * $hours;

        $reservation = Reservation::create([
            'court_id'         => $court->id,
            'user_id'          => auth()->id(),
            'reservation_date' => $data['reservation_date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'total_price'      => $price,
            'status'           => 'confirmed',
            'payment_status'   => $data['payment_status'],
            'notes'            => $data['notes'] ?? null,
            'client_name'      => $data['client_name'],
            'client_phone'     => $data['client_phone'] ?? null,
            'is_manual'        => true,
        ]);

        return response()->json(['message' => 'Reserva manual creada.', 'reservation' => $reservation]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $this->authorizeOwner($reservation);

        $data = $request->validate([
            'client_name'    => 'sometimes|string|max:150',
            'client_phone'   => 'nullable|string|max:30',
            'payment_status' => 'sometimes|in:pending,verified',
            'status'         => 'sometimes|in:confirmed,cancelled,no_show',
            'notes'          => 'nullable|string|max:500',
        ]);

        $reservation->update($data);
        return response()->json(['message' => 'Reserva actualizada.']);
    }

    public function confirm(Reservation $reservation)
    {
        $this->authorizeOwner($reservation);
        $reservation->update(['status' => 'confirmed', 'payment_status' => 'verified']);
        $this->notifications->reservationConfirmed($reservation);
        return response()->json(['message' => 'Reserva confirmada.']);
    }

    public function reject(Request $request, Reservation $reservation)
    {
        $this->authorizeOwner($reservation);
        $reservation->update(['status' => 'cancelled', 'payment_status' => 'rejected']);
        $this->notifications->reservationCancelled($reservation);
        return response()->json(['message' => 'Reserva rechazada.']);
    }

    public function calendarData(Request $request)
    {
        $courtIds = auth()->user()->venues()
            ->with('courts')->get()
            ->flatMap(fn($v) => $v->courts->pluck('id'));

        $start = $request->input('start', now()->startOfWeek()->toDateString());
        $end   = $request->input('end',   now()->endOfWeek()->toDateString());

        $query = Reservation::with(['court', 'user'])
            ->whereIn('court_id', $courtIds)
            ->whereBetween('reservation_date', [$start, $end])
            ->whereIn('status', ['pending', 'confirmed']);

        if ($request->court) $query->where('court_id', $request->court);

        $colorMap = ['confirmed' => '#17c653', 'pending' => '#f6c000'];

        return response()->json($query->get()->map(fn($r) => [
            'id'    => $r->id,
            'title' => ($r->is_manual ? ($r->client_name ?? 'Manual') : $r->user->name) . ' · ' . $r->court->name,
            'start' => $r->reservation_date->format('Y-m-d') . 'T' . $r->start_time,
            'end'   => $r->reservation_date->format('Y-m-d') . 'T' . $r->end_time,
            'color' => $colorMap[$r->status] ?? '#888',
            'extendedProps' => [
                'status'         => $r->status,
                'is_manual'      => $r->is_manual,
                'user'           => $r->is_manual ? ($r->client_name ?? '—') : $r->user->name,
                'phone'          => $r->is_manual ? ($r->client_phone ?? '—') : ($r->user->phone ?? '—'),
                'payment_status' => $r->payment_status,
                'proof_url'      => $r->proof_url,
                'notes'          => $r->notes,
                'court'          => $r->court->name,
                'total'          => $r->total_price,
                'discount'       => $r->discount_amount,
            ],
        ]));
    }

    public function show(Reservation $reservation)
    {
        $this->authorizeOwner($reservation);
        $reservation->load(['court.venue', 'user', 'services.extraService', 'promotion']);
        return response()->json($reservation);
    }

    private function authorizeOwner(Reservation $reservation): void
    {
        if ($reservation->court->venue->owner_id !== auth()->id()) abort(403);
    }
}
