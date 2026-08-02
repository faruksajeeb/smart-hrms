<?php

namespace App\Services\HR\Leave;

use App\Enums\HolidayStatus;
use App\Models\HolidayCalendar;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HolidayService
{
    public function create(array $data, ?int $userId = null): HolidayCalendar
    {
        return HolidayCalendar::create([
            'holiday_name' => $data['holiday_name'],
            'holiday_code' => $data['holiday_code'],
            'holiday_date' => $data['holiday_date'],
            'holiday_type' => $data['holiday_type'],
            'is_recurring' => $data['is_recurring'] ?? false,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? HolidayStatus::Active,
            'created_by' => $userId ?? Auth::id(),
            'updated_by' => $userId ?? Auth::id(),
        ]);
    }

    public function update(HolidayCalendar $holiday, array $data, ?int $userId = null): HolidayCalendar
    {
        $holiday->update([
            'holiday_name' => $data['holiday_name'] ?? $holiday->holiday_name,
            'holiday_code' => $data['holiday_code'] ?? $holiday->holiday_code,
            'holiday_date' => $data['holiday_date'] ?? $holiday->holiday_date,
            'holiday_type' => $data['holiday_type'] ?? $holiday->holiday_type,
            'is_recurring' => $data['is_recurring'] ?? $holiday->is_recurring,
            'description' => $data['description'] ?? $holiday->description,
            'status' => $data['status'] ?? $holiday->status,
            'updated_by' => $userId ?? Auth::id(),
        ]);

        return $holiday->fresh();
    }

    public function delete(HolidayCalendar $holiday): void
    {
        $holiday->delete();
    }

    public function getActiveHolidays()
    {
        return HolidayCalendar::where('status', HolidayStatus::Active)
            ->orderBy('holiday_date', 'asc')
            ->get();
    }
}
