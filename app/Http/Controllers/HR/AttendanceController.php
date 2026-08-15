<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ShiftSchedule;
use App\Models\AttendancePunch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $openEntry = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        $history = Attendance::where('user_id', $user->id)
            ->with('schedule.shift')
            ->latest('clock_in')
            ->limit(20)
            ->get();

        $todaysSchedule = ShiftSchedule::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->with('shift')
            ->first();

        return Inertia::render('Attendance/Index', [
            'openEntry' => $openEntry,
            'history' => $history,
            'todaysSchedule' => $todaysSchedule,
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if(
            Attendance::where('user_id', $user->id)->whereNull('clock_out')->exists(),
            409,
            'You are already clocked in.'
        );

        $data = $request->validate([
            'shift_schedule_id' => ['nullable', 'exists:shift_schedules,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $schedule = isset($data['shift_schedule_id'])
            ? ShiftSchedule::find($data['shift_schedule_id'])
            : ShiftSchedule::where('user_id', $user->id)->where('work_date', now()->toDateString())->first();

        $status = 'on_time';
        if ($schedule) {
            $scheduledStart = now()->setTimeFromTimeString($schedule->start_time ?? $schedule->shift->start_time);
            $status = now()->greaterThan($scheduledStart->copy()->addMinutes(10)) ? 'late' : 'on_time';
        }

        Attendance::create([
            'user_id' => $user->id,
            'shift_schedule_id' => $schedule?->id,
            'clock_in' => now(),
            'clock_in_latitude' => $data['latitude'] ?? null,
            'clock_in_longitude' => $data['longitude'] ?? null,
            'status' => $status,
        ]);
        AttendancePunch::create(['user_id'=>$user->id,'punch_datetime'=>now(),'punch_date'=>today(),'punch_type'=>'in','source'=>'web','latitude'=>$data['latitude'] ?? null,'longitude'=>$data['longitude'] ?? null]);

        return back()->with('success', 'Clocked in.');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
            'clock_out_latitude' => $data['latitude'] ?? null,
            'clock_out_longitude' => $data['longitude'] ?? null,
            'total_minutes' => now()->diffInMinutes($attendance->clock_in),
        ]);
        AttendancePunch::create(['user_id'=>$request->user()->id,'punch_datetime'=>now(),'punch_date'=>today(),'punch_type'=>'out','source'=>'web','latitude'=>$data['latitude'] ?? null,'longitude'=>$data['longitude'] ?? null]);

        return back()->with('success', 'Clocked out.');
    }
}
