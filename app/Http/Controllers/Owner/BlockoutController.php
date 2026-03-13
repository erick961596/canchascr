<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Blockout;
use App\Models\Court;
use Illuminate\Http\Request;

class BlockoutController extends Controller
{
    public function store(Request $request, Court $court)
    {
        abort_unless($court->venue->owner_id === auth()->id(), 403);

        $data = $request->validate([
            'block_date' => 'required|date|after_or_equal:today',
            'full_day'   => 'boolean',
            'start_time' => 'required_if:full_day,false|nullable|date_format:H:i',
            'end_time'   => 'required_if:full_day,false|nullable|date_format:H:i|after:start_time',
            'reason'     => 'nullable|string|max:200',
        ]);
        $data['court_id'] = $court->id;

        $blockout = Blockout::create($data);
        return response()->json(['message' => 'Bloqueo creado.', 'blockout' => $blockout]);
    }

    public function destroy(Blockout $blockout)
    {
        abort_unless($blockout->court->venue->owner_id === auth()->id(), 403);
        $blockout->delete();
        return response()->json(['message' => 'Bloqueo eliminado.']);
    }
}
