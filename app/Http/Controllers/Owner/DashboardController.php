<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index()
    {
        $owner  = auth()->user();
        $venues = $owner->venues()->with('activeCourts')->get();
        $courtIds = $venues->flatMap(fn($v) => $v->activeCourts->pluck('id'));

        $stats = [
            'total_venues'       => $venues->count(),
            'total_courts'       => $courtIds->count(),
            'pending_reservations' => Reservation::whereIn('court_id', $courtIds)
                ->where('status', 'pending')->count(),
            'confirmed_today'    => Reservation::whereIn('court_id', $courtIds)
                ->where('status', 'confirmed')
                ->whereDate('reservation_date', today())->count(),
            'revenue_month'      => Reservation::whereIn('court_id', $courtIds)
                ->where('status', 'confirmed')
                ->whereMonth('reservation_date', now()->month)
                ->sum('total_price'),
        ];

        $recentReservations = Reservation::with(['court.venue', 'user'])
            ->whereIn('court_id', $courtIds)
            ->latest()
            ->take(8)
            ->get();

        $monthlyData = Reservation::whereIn('court_id', $courtIds)
            ->where('status', 'confirmed')
            ->whereYear('reservation_date', now()->year)
            ->selectRaw('MONTH(reservation_date) as month, SUM(total_price) as revenue, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('revenue', 'month');

        return view('pages.owner.dashboard.index', compact('stats', 'recentReservations', 'monthlyData', 'venues'));
    }
}
