<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;

class AvailabilityService
{
    public function getAvailableSlots(
        int $tenantId,
        int $barberId,
        string $date,
        int $duration
    ): array {

        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;

        $schedules = Schedule::where('tenant_id', $tenantId)
            ->where('user_id', $barberId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $booked = Appointment::where('tenant_id', $tenantId)
            ->where('barber_id', $barberId)
            ->where('appointment_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->get(['start_time', 'end_time']);

        $slots = [];

        foreach ($schedules as $schedule) {

            $current = Carbon::parse($date . ' ' . $schedule->opens_at);
            $end = Carbon::parse($date . ' ' . $schedule->closes_at);

            while ($current->copy()->addMinutes($duration)->lte($end)) {

                $slotStart = $current->format('H:i:s');
                $slotEnd = $current->copy()->addMinutes($duration)->format('H:i:s');

                $free = true;

                foreach ($booked as $appointment) {

                    if (
                        $slotStart < $appointment->end_time &&
                        $slotEnd > $appointment->start_time
                    ) {
                        $free = false;
                        break;
                    }
                }

                if (
                    $free &&
                    (
                        !$carbon->isToday() ||
                        $current->greaterThan(now()->addMinutes(15))
                    )
                ) {
                    $slots[] = [
                        'start' => substr($slotStart, 0, 5),
                        'end' => substr($slotEnd, 0, 5),
                        'available' => true,
                    ];
                }

                $current->addMinutes(30);
            }
        }

        return $slots;
    }
}