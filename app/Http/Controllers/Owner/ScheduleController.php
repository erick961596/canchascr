<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Court $court)
    {
        abort_unless($court->venue->owner_id === auth()->id(), 403);
        $schedules = $court->schedules()->get()->keyBy('day_of_week');
        return response()->json($schedules);
    }

    public function bulkUpdate(Request $request, Court $court)
    {
        abort_unless($court->venue->owner_id === auth()->id(), 403);

        $request->validate([
            'schedules'                => 'required|array',
            'schedules.*.day_of_week'  => 'required|in:mon,tue,wed,thu,fri,sat,sun',
            'schedules.*.open_time'    => 'required|date_format:H:i',
            'schedules.*.close_time'   => 'required|date_format:H:i|after:schedules.*.open_time',
            'schedules.*.active'       => 'boolean',
        ]);

        foreach ($request->schedules as $item) {
            Schedule::updateOrCreate(
                ['court_id' => $court->id, 'day_of_week' => $item['day_of_week']],
                ['open_time' => $item['open_time'], 'close_time' => $item['close_time'], 'active' => $item['active'] ?? true]
            );
        }

        return response()->json(['message' => 'Horarios guardados.']);
    }

    public function store(Request $request, Court $court)
    {
        abort_unless($court->venue->owner_id === auth()->id(), 403);

        $data = $request->validate([
            'day_of_week' => 'required|in:mon,tue,wed,thu,fri,sat,sun',
            'open_time'   => 'required|date_format:H:i',
            'close_time'  => 'required|date_format:H:i',
            'active'      => 'boolean',
        ]);
        $data['court_id'] = $court->id;

        $schedule = Schedule::updateOrCreate(
            ['court_id' => $court->id, 'day_of_week' => $data['day_of_week']],
            $data
        );

        return response()->json(['message' => 'Horario guardado.', 'schedule' => $schedule]);
    }
}
