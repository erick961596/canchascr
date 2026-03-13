<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Venue;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $venues = auth()->user()->venues()
            ->with([
                'courts:id,venue_id,name',
                'promotions' => fn($q) => $q->orderByDesc('starts_at'),
            ])
            ->get();

        return view('pages.owner.promotions.index', compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venue_id'    => 'required|uuid',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after_or_equal:starts_at',
            'court_ids'   => 'nullable|array',
            'court_ids.*' => 'uuid',
        ]);

        $venue = Venue::where('owner_id', auth()->id())->findOrFail($data['venue_id']);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return response()->json(['message' => 'El porcentaje no puede superar 100%.'], 422);
        }

        $promo = Promotion::create(array_merge($data, ['active' => true]));

        return response()->json(['message' => 'Promoción creada.', 'promotion' => $promo]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $this->authorizeOwner($promotion);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'type'        => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'starts_at'   => 'required|date',
            'ends_at'     => 'required|date|after_or_equal:starts_at',
            'court_ids'   => 'nullable|array',
            'court_ids.*' => 'uuid',
            'active'      => 'boolean',
        ]);

        $promotion->update($data);

        return response()->json(['message' => 'Promoción actualizada.', 'promotion' => $promotion]);
    }

    public function destroy(Promotion $promotion)
    {
        $this->authorizeOwner($promotion);
        $promotion->delete();
        return response()->json(['message' => 'Promoción eliminada.']);
    }

    private function authorizeOwner(Promotion $promotion): void
    {
        if ($promotion->venue->owner_id !== auth()->id()) abort(403);
    }
}
