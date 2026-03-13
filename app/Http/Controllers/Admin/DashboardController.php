<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use App\Models\Reservation;
use App\Models\Subscription;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'         => User::where('role', 'user')->count(),
            'total_owners'        => User::where('role', 'owner')->count(),
            'total_venues'        => Venue::count(),
            'active_venues'       => Venue::where('active', true)->count(),
            'active_subscriptions'=> Subscription::where('status', 'active')->count(),
            'pending_subscriptions'=> Subscription::where('status', 'pending')->count(),
            'total_reservations'  => Reservation::count(),
            'revenue_month'       => Subscription::where('status', 'active')
                ->whereMonth('last_payment_at', now()->month)->sum('price'),
        ];

        $recentSubscriptions = Subscription::with(['user', 'plan'])
            ->latest()->take(8)->get();

        $monthlyRevenue = Subscription::where('status', 'active')
            ->selectRaw('MONTH(last_payment_at) as month, SUM(price) as revenue')
            ->whereYear('last_payment_at', now()->year)
            ->groupBy('month')
            ->pluck('revenue', 'month');

        return view('pages.admin.dashboard.index', compact('stats', 'recentSubscriptions', 'monthlyRevenue'));
    }
}
