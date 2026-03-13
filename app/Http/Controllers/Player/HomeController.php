<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\{Venue, Court};
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredVenues = Venue::active()
            ->with(['courts' => fn($q) => $q->active()])
            ->latest()
            ->take(6)
            ->get();

        return view('pages.player.home.index', compact('featuredVenues'));
    }

    public function explore(Request $request)
    {
        $query = Venue::active()->with(['activeCourts']);

        if ($request->province)  $query->byProvince($request->province);
        if ($request->canton)    $query->byCanton($request->canton);
        if ($request->district)  $query->byDistrict($request->district);

        if ($request->sport) {
            $query->whereHas('courts', fn($q) =>
                $q->where('sport', $request->sport)->where('active', true)
            );
        }

        if ($request->feature) {
            $query->whereHas('courts', fn($q) =>
                $q->whereJsonContains('features', $request->feature)->where('active', true)
            );
        }

        $venues = $query->paginate(12)->withQueryString();

        $provinces = \DB::table('location_data')->distinct()->pluck('province')->sort()->values();
        $cantons   = $request->province
            ? \DB::table('location_data')->where('province', $request->province)->distinct()->pluck('canton')->sort()->values()
            : collect();
        $districts = $request->canton
            ? \DB::table('location_data')->where('canton', $request->canton)->distinct()->pluck('district')->sort()->values()
            : collect();

        $sports = ['futbol','basquetbol','tenis','padel','volleyball','beisbol','otro'];

        return view('pages.player.explore.index', compact(
            'venues','provinces','cantons','districts','sports'
        ));
    }

    public function venueDetail(string $slug)
    {
        $venue = Venue::active()
            ->where('slug', $slug)
            ->with(['activeCourts.schedules', 'owner'])
            ->firstOrFail();

        return view('pages.player.explore.venue', compact('venue'));
    }

    public function courtDetail(string $venueSlug, string $courtId)
    {
        $venue = Venue::active()->where('slug', $venueSlug)->firstOrFail();
        $court = $venue->activeCourts()->findOrFail($courtId);

        // Active promotions for this court
        $today = now()->toDateString();
        $promotions = \App\Models\Promotion::where('venue_id', $venue->id)
            ->where('active', true)
            ->where('starts_at', '<=', $today)
            ->where('ends_at', '>=', $today)
            ->get()
            ->filter(fn($p) => $p->appliesToCourt($court->id))
            ->values();

        // Active extra services for this venue
        $extraServices = \App\Models\ExtraService::where('venue_id', $venue->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('pages.player.explore.court', compact('venue', 'court', 'promotions', 'extraServices'));
    }

    // AJAX: get slots for a date
    public function getSlots(Request $request, string $courtId)
    {
        $court = Court::with(['schedules', 'blockouts'])->findOrFail($courtId);
        $date  = $request->validate(['date' => 'required|date|after_or_equal:today'])['date'];

        $slots = app(\App\Services\AvailabilityService::class)->getSlots($court, $date);

        return response()->json($slots);
    }
}
