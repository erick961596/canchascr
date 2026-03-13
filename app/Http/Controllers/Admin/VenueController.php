<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Venue, Court};
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $venues = Venue::with(['owner', 'courts', 'ratings'])
            ->withCount(['courts', 'ratings'])
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('province', 'like', '%'.$request->search.'%')
            )
            ->when($request->active !== null, fn($q) => $q->where('active', $request->active))
            ->latest()
            ->paginate(15);

        return view('pages.admin.venues.index', compact('venues'));
    }

    public function show(Venue $venue)
    {
        $venue->load(['owner','courts.schedules','ratings.user']);
        return view('pages.admin.venues.show', compact('venue'));
    }

    public function toggle(Venue $venue)
    {
        $venue->update(['active' => !$venue->active]);
        return response()->json(['active' => $venue->active, 'message' => $venue->active ? 'Sede activada.' : 'Sede desactivada.']);
    }
}
