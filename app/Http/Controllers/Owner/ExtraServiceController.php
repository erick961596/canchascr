<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ExtraService;
use App\Models\Venue;
use Illuminate\Http\Request;

class ExtraServiceController extends Controller
{
    public function index()
    {
        $venues = auth()->user()->venues()->with(['extraServices' => fn($q) => $q->orderBy('name')])->get();
        return view('pages.owner.extra-services.index', compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venue_id'    => 'required|uuid',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'price'       => 'required|numeric|min:0',
        ]);

        $venue = Venue::where('owner_id', auth()->id())->findOrFail($data['venue_id']);

        $service = ExtraService::create(array_merge($data, ['active' => true]));

        return response()->json(['message' => 'Servicio creado.', 'service' => $service]);
    }

    public function update(Request $request, ExtraService $extraService)
    {
        $this->authorizeOwner($extraService);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
            'price'       => 'required|numeric|min:0',
            'active'      => 'boolean',
        ]);

        $extraService->update($data);

        return response()->json(['message' => 'Servicio actualizado.', 'service' => $extraService]);
    }

    public function destroy(ExtraService $extraService)
    {
        $this->authorizeOwner($extraService);
        $extraService->delete();
        return response()->json(['message' => 'Servicio eliminado.']);
    }

    private function authorizeOwner(ExtraService $service): void
    {
        if ($service->venue->owner_id !== auth()->id()) abort(403);
    }
}
