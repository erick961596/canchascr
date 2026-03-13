<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Court, Venue, Schedule, Blockout};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourtController extends Controller
{
    public function index()
    {
        $venues = auth()->user()->venues()->with(['courts.schedules', 'courts.blockouts'])->get();
        return view('pages.owner.courts.index', compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venue_id'       => 'required|uuid',
            'name'           => 'required|string|max:100',
            'sport'          => 'required|in:futbol,basquetbol,tenis,padel,volleyball,beisbol,otro',
            'price_per_hour' => 'required|numeric|min:0',
            'slot_duration'  => 'required|in:30,60,90,120',
            'features'       => 'nullable|array',
            'images.*'       => 'nullable|image|max:5120',
        ]);

        $venue = Venue::where('owner_id', auth()->id())->findOrFail($data['venue_id']);

        if (!auth()->user()->canAddCourt()) {
            return response()->json(['message' => 'Alcanzaste el límite de canchas de tu plan.'], 422);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('courts/images/' . $venue->id, 's3');
                if ($path) $images[] = $path;
            }
        }

        $court = Court::create(array_merge($data, ['images' => $images]));

        return response()->json([
            'message' => 'Cancha creada exitosamente.',
            'court'   => $court->load('venue'),
        ]);
    }

    public function update(Request $request, Court $court)
    {
        $this->authorizeOwner($court);

        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'sport'          => 'required|in:futbol,basquetbol,tenis,padel,volleyball,beisbol,otro',
            'price_per_hour' => 'required|numeric|min:0',
            'slot_duration'  => 'required|in:30,60,90,120',
            'features'       => 'nullable|array',
            'active'         => 'boolean',
        ]);

        $court->update($data);

        return response()->json(['message' => 'Cancha actualizada.', 'court' => $court]);
    }

    public function destroy(Court $court)
    {
        $this->authorizeOwner($court);
        $court->update(['active' => false]);
        return response()->json(['message' => 'Cancha desactivada.']);
    }

    // -----------------------------------------------------------------------
    // Horarios — GET (para cargar en modal)
    // -----------------------------------------------------------------------
    public function getSchedules(Court $court)
    {
        $this->authorizeOwner($court);
        return response()->json($court->schedules);
    }

    // -----------------------------------------------------------------------
    // Horarios — POST (guardar / actualizar)
    // -----------------------------------------------------------------------
    public function saveSchedules(Request $request, Court $court)
    {
        $this->authorizeOwner($court);

        $data = $request->validate([
            'schedules'               => 'required|array|min:1',
            'schedules.*.day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat,sun',
            'schedules.*.open_time'   => 'required|date_format:H:i,H:i:s',
            'schedules.*.close_time'  => 'required|date_format:H:i,H:i:s',
            'schedules.*.active'      => 'boolean',
        ]);

        foreach ($data['schedules'] as $s) {
            $open  = substr($s['open_time'],  0, 5);
            $close = substr($s['close_time'], 0, 5);

            if ($close <= $open) {
                return response()->json([
                    'message' => "El cierre debe ser posterior a la apertura ({$s['day_of_week']})."
                ], 422);
            }

            Schedule::updateOrCreate(
                ['court_id' => $court->id, 'day_of_week' => $s['day_of_week']],
                ['open_time' => $open, 'close_time' => $close, 'active' => $s['active'] ?? true]
            );
        }

        return response()->json(['message' => 'Horarios guardados.']);
    }

    // -----------------------------------------------------------------------
    // Bloqueos — GET
    // -----------------------------------------------------------------------
    public function getBlockouts(Court $court)
    {
        $this->authorizeOwner($court);
        return response()->json(
            $court->blockouts()->orderBy('block_date')->get()
        );
    }

    // -----------------------------------------------------------------------
    // Bloqueos — POST
    // -----------------------------------------------------------------------
    public function addBlockout(Request $request, Court $court)
    {
        $this->authorizeOwner($court);

        $data = $request->validate([
            'block_date' => 'required|date|after_or_equal:today',
            'full_day'   => 'boolean',
            'start_time' => 'required_if:full_day,false|nullable|date_format:H:i',
            'end_time'   => 'required_if:full_day,false|nullable|date_format:H:i',
            'reason'     => 'nullable|string|max:200',
        ]);

        $blockout = Blockout::create(array_merge($data, ['court_id' => $court->id]));

        return response()->json(['message' => 'Bloqueo agregado.', 'blockout' => $blockout]);
    }

    // -----------------------------------------------------------------------
    // Bloqueos — DELETE
    // -----------------------------------------------------------------------
    public function removeBlockout(Blockout $blockout)
    {
        $this->authorizeOwner($blockout->court);
        $blockout->delete();
        return response()->json(['message' => 'Bloqueo eliminado.']);
    }

    private function authorizeOwner(Court $court): void
    {
        if ($court->venue->owner_id !== auth()->id()) abort(403);
    }
}
