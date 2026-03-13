<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\{Court, Reservation, ReservationService, Promotion};
use App\Services\{AvailabilityService, NotificationService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class ReservationController extends Controller
{
    public function __construct(
        private AvailabilityService $availability,
        private NotificationService $notifications
    ) {}

    public function index()
    {
        $reservations = Reservation::with(['court.venue', 'services.extraService'])
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
            'promotion_id'     => 'nullable|uuid|exists:promotions,id',
            'extra_services'   => 'nullable|array',
            'extra_services.*.id'       => 'required|uuid|exists:extra_services,id',
            'extra_services.*.quantity' => 'required|integer|min:1|max:20',
        ]);

        $court = Court::findOrFail($data['court_id']);

        // Verify slot still available
        $slots = $this->availability->getSlots($court, $data['reservation_date']);
        $slotOk = collect($slots)->first(fn($s) =>
            $s['start'] === $data['start_time'] && $s['end'] === $data['end_time'] && $s['available']
        );

        if (!$slotOk) {
            return response()->json(['message' => 'El horario ya no está disponible.'], 422);
        }

        $hours     = (strtotime($data['end_time']) - strtotime($data['start_time'])) / 3600;
        $basePrice = $court->price_per_hour * $hours;

        // Apply promotion if provided
        $discountAmount = 0;
        $promotionId    = null;

        if (!empty($data['promotion_id'])) {
            $promo = Promotion::find($data['promotion_id']);
            if ($promo && $promo->isActiveNow() && $promo->appliesToCourt($court->id)) {
                $discountAmount = $promo->calculateDiscount((float) $basePrice);
                $promotionId    = $promo->id;
            }
        }

        // Calculate extras total
        $extrasTotal = 0;
        $extrasToSave = [];

        if (!empty($data['extra_services'])) {
            $serviceIds = collect($data['extra_services'])->pluck('id');
            $services   = \App\Models\ExtraService::whereIn('id', $serviceIds)
                ->where('venue_id', $court->venue_id)
                ->where('active', true)
                ->get()
                ->keyBy('id');

            foreach ($data['extra_services'] as $es) {
                $svc = $services->get($es['id']);
                if (!$svc) continue;
                $qty = (int) $es['quantity'];
                $extrasTotal += $svc->price * $qty;
                $extrasToSave[] = [
                    'extra_service_id' => $svc->id,
                    'price_snapshot'   => $svc->price,
                    'quantity'         => $qty,
                ];
            }
        }

        $totalPrice = $basePrice - $discountAmount + $extrasTotal;

        $reservation = DB::transaction(function () use (
            $court, $data, $basePrice, $discountAmount, $promotionId, $extrasToSave, $totalPrice
        ) {
            $res = Reservation::create([
                'court_id'         => $court->id,
                'user_id'          => auth()->id(),
                'reservation_date' => $data['reservation_date'],
                'start_time'       => $data['start_time'],
                'end_time'         => $data['end_time'],
                'total_price'      => $totalPrice,
                'status'           => 'pending',
                'payment_status'   => 'pending',
                'notes'            => $data['notes'] ?? null,
                'promotion_id'     => $promotionId,
                'discount_amount'  => $discountAmount,
            ]);

            foreach ($extrasToSave as $es) {
                ReservationService::create(array_merge($es, ['reservation_id' => $res->id]));
            }

            return $res;
        });

        $this->notifications->reservationCreated($reservation);

        return response()->json([
            'message'        => '¡Reserva creada! Subí el comprobante de pago.',
            'reservation_id' => $reservation->id,
        ]);
    }

    public function uploadProof(Request $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $request->validate(['proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);

        $path = $request->file('proof')->store(
            'reservations/proofs/' . $reservation->id, 's3'
        );

        $reservation->update(['payment_proof' => $path, 'payment_status' => 'pending']);

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
