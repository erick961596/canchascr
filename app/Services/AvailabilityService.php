<?php

namespace App\Services;

use App\Models\Court;
use App\Models\Blockout;
use App\Models\Reservation;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Returns available time slots for a court on a given date.
     * Each slot: ['start' => '08:00', 'end' => '09:00', 'available' => bool]
     */
    public function getSlots(Court $court, string $date): array
    {
        $carbon = Carbon::parse($date);
        $dayMap = ['Mon'=>'mon','Tue'=>'tue','Wed'=>'wed','Thu'=>'thu','Fri'=>'fri','Sat'=>'sat','Sun'=>'sun'];
        $dayKey = $dayMap[$carbon->format('D')] ?? null;

        $schedule = $court->schedules()
            ->where('day_of_week', $dayKey)
            ->where('active', true)
            ->first();

        if (!$schedule) return [];

        $fullDayBlock = Blockout::where('court_id', $court->id)
            ->where('block_date', $date)
            ->where('full_day', true)
            ->exists();

        if ($fullDayBlock) return [];

        $blockedRanges = Blockout::where('court_id', $court->id)
            ->where('block_date', $date)
            ->where('full_day', false)
            ->get(['start_time','end_time'])
            ->toArray();

        $reservedRanges = Reservation::where('court_id', $court->id)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending','confirmed'])
            ->get(['start_time','end_time'])
            ->toArray();

        $slots = [];
        $current = Carbon::parse($schedule->open_time);
        $end     = Carbon::parse($schedule->close_time);
        $duration = $court->slot_duration;

        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $slotStart = $current->format('H:i');
            $slotEnd   = $current->copy()->addMinutes($duration)->format('H:i');

            $blocked = $this->overlaps($slotStart, $slotEnd, $blockedRanges)
                    || $this->overlaps($slotStart, $slotEnd, $reservedRanges);

            $slots[] = [
                'start'     => $slotStart,
                'end'       => $slotEnd,
                'available' => !$blocked,
                'price'     => $court->price_per_hour * ($duration / 60),
            ];

            $current->addMinutes($duration);
        }

        return $slots;
    }

    private function overlaps(string $start, string $end, array $ranges): bool
    {
        foreach ($ranges as $r) {
            $rStart = $r['start_time'];
            $rEnd   = $r['end_time'];
            if ($start < $rEnd && $end > $rStart) return true;
        }
        return false;
    }
}
