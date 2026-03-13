<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenueController extends Controller
{
    public function index()
    {
        $venues = auth()->user()->venues()->with('activeCourts')->get();
        $provinces = \DB::table('location_data')->distinct()->pluck('province')->sort()->values();
        return view('pages.owner.venues.index', compact('venues', 'provinces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:200',
            'province'    => 'required|string',
            'canton'      => 'required|string',
            'district'    => 'required|string',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'logo'        => 'nullable|image|max:5120',
            'images.*'    => 'nullable|image|max:5120',
            'amenities'   => 'nullable|array',
        ]);

        $logo = null;
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo')->store('venues/logos', 's3');
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $images[] = $img->store('venues/images/' . time(), 's3');
            }
        }

        $venue = Venue::create(array_merge($data, [
            'owner_id' => auth()->id(),
            'logo'     => $logo,
            'images'   => $images,
        ]));

        return response()->json([
            'message' => 'Sede creada.',
            'venue'   => $venue,
        ]);
    }

    public function update(Request $request, Venue $venue)
    {
        if ($venue->owner_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:200',
            'province'    => 'required|string',
            'canton'      => 'required|string',
            'district'    => 'required|string',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'amenities'   => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            if ($venue->logo) Storage::disk('s3')->delete($venue->logo);
            $data['logo'] = $request->file('logo')->store('venues/logos', 's3');
        }

        $venue->update($data);

        return response()->json(['message' => 'Sede actualizada.', 'venue' => $venue]);
    }

    public function getCantons(Request $request)
    {
        $cantons = \DB::table('location_data')
            ->where('province', $request->province)
            ->distinct()->pluck('canton')->sort()->values();
        return response()->json($cantons);
    }

    public function getDistricts(Request $request)
    {
        $districts = \DB::table('location_data')
            ->where('province', $request->province)
            ->where('canton', $request->canton)
            ->distinct()->pluck('district')->sort()->values();
        return response()->json($districts);
    }
}
