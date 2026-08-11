<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ShiftScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $start = (string) $request->string('start');
        $end = (string) $request->string('end');

        if ($start === '' || $end === '') {
            $start = Carbon::today()->startOfWeek()->format('Y-m-d');
            $end = Carbon::today()->endOfWeek()->format('Y-m-d');
        }

        $schedules = ShiftSchedule::query()
            ->with(['user:id,name,employee_id', 'shift:id,name,color,start_time,end_time,is_overnight'])
            ->whereDate('work_date', '>=', $start)
            ->whereDate('work_date', '<=', $end)
            ->orderBy('work_date')
            ->orderBy('start_time')
            ->get()
            ->map(fn (ShiftSchedule $schedule) => $this->toArray($schedule));

        return Inertia::render('HR/ShiftSchedules/Index', [
            'schedules' => $schedules,
            'shifts' => $this->shiftOptions(),
            'employees' => $this->employeeOptions(),
            'range' => ['start' => $start, 'end' => $end],
            'canManage' => $request->user()->can('manage attendance'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSchedule($request);
        $data['status'] = $data['status'] ?? ShiftSchedule::STATUS_SCHEDULED;
        $data['created_by'] = Auth::id();

        ShiftSchedule::create($data);

        return redirect()
            ->route('hr.shift-schedules.index')
            ->with('success', 'Shift schedule has been created.');
    }

    public function update(Request $request, ShiftSchedule $shiftSchedule): RedirectResponse
    {
        $shiftSchedule->update($this->validateSchedule($request, $shiftSchedule));

        return redirect()
            ->route('hr.shift-schedules.index')
            ->with('success', 'Shift schedule has been updated.');
    }

    public function destroy(ShiftSchedule $shiftSchedule): RedirectResponse
    {
        $shiftSchedule->delete();

        return redirect()
            ->route('hr.shift-schedules.index')
            ->with('success', 'Shift schedule has been deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSchedule(Request $request, ?ShiftSchedule $schedule = null): array
    {
        return $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('shift_schedules')
                    ->where(fn ($query) => $query
                        ->where('shift_id', $request->input('shift_id'))
                        ->whereDate('work_date', $request->input('work_date')))
                    ->ignore($schedule?->id),
            ],
            'shift_id' => ['required', 'exists:shifts,id'],
            'work_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['nullable', Rule::in(ShiftSchedule::statuses())],
            'notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function employeeOptions(): array
    {
        return User::query()
            ->select(['id', 'name', 'employee_id'])
            ->where(function ($query) {
                $query->whereHas('employeeProfile')
                    ->orWhereHas('roles', fn ($roles) => $roles->where('name', User::ROLE_EMPLOYEE));
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => trim("{$user->name}".($user->employee_id ? " ({$user->employee_id})" : '')),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function shiftOptions(): array
    {
        return Shift::query()
            ->orderBy('name')
            ->get(['id', 'name', 'start_time', 'end_time', 'color', 'is_active'])
            ->map(fn (Shift $shift) => [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => $shift->start_time ? Carbon::parse($shift->start_time)->format('H:i') : null,
                'end_time' => $shift->end_time ? Carbon::parse($shift->end_time)->format('H:i') : null,
                'color' => $shift->color,
                'is_active' => $shift->is_active,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function toArray(ShiftSchedule $schedule): array
    {
        $time = fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null;

        return [
            'id' => $schedule->id,
            'work_date' => $schedule->getRawOriginal('work_date'),
            'start_time' => $time($schedule->start_time),
            'end_time' => $time($schedule->end_time),
            'status' => $schedule->status,
            'notes' => $schedule->notes,
            'shift' => $schedule->shift ? [
                'id' => $schedule->shift->id,
                'name' => $schedule->shift->name,
                'color' => $schedule->shift->color,
                'start_time' => $time($schedule->shift->start_time),
                'end_time' => $time($schedule->shift->end_time),
                'is_overnight' => $schedule->shift->is_overnight,
            ] : null,
            'user' => $schedule->user ? [
                'id' => $schedule->user->id,
                'name' => $schedule->user->name,
                'employee_id' => $schedule->user->employee_id,
            ] : null,
        ];
    }
}
