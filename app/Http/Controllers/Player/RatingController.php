<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\{Venue, VenueRating};
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Venue $venue)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Must have a confirmed reservation at this venue
        $hasVisited = auth()->user()->reservations()
            ->whereHas('court', fn($q) => $q->where('venue_id', $venue->id))
            ->where('status', 'confirmed')
            ->exists();

        if (!$hasVisited) {
            return response()->json(['message' => 'Solo podés calificar sedes donde hayas reservado.'], 403);
        }

        VenueRating::updateOrCreate(
            ['venue_id' => $venue->id, 'user_id' => auth()->id()],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        $avg   = round($venue->ratings()->avg('rating'), 1);
        $count = $venue->ratings()->count();

        return response()->json(['message' => '¡Gracias por tu calificación!', 'avg' => $avg, 'count' => $count]);
    }
}
