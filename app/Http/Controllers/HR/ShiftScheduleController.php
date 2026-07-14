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
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $shiftId = trim((string) $request->string('shift_id'));
        $fromDate = trim((string) $request->string('from_date'));
        $toDate = trim((string) $request->string('to_date'));

        $schedules = ShiftSchedule::query()
            ->with(['user:id,name,employee_id', 'shift:id,name,color,start_time,end_time'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', function ($user) use ($search) {
                        $user->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    })->orWhereHas('shift', fn ($shift) => $shift->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($shiftId !== '', fn ($query) => $query->where('shift_id', $shiftId))
            ->when($fromDate !== '', fn ($query) => $query->whereDate('work_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('work_date', '<=', $toDate))
            ->orderByDesc('work_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ShiftSchedule $schedule) => $this->toArray($schedule));

        $statsQuery = ShiftSchedule::query()
            ->when($fromDate !== '', fn ($query) => $query->whereDate('work_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('work_date', '<=', $toDate));

        return Inertia::render('HR/ShiftSchedules/Index', [
            'schedules' => $schedules,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'shift_id' => $shiftId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'scheduled' => (clone $statsQuery)->where('status', ShiftSchedule::STATUS_SCHEDULED)->count(),
                'confirmed' => (clone $statsQuery)->where('status', ShiftSchedule::STATUS_CONFIRMED)->count(),
                'completed' => (clone $statsQuery)->where('status', ShiftSchedule::STATUS_COMPLETED)->count(),
            ],
            'options' => [
                'employees' => $this->employeeOptions(),
                'shifts' => $this->shiftOptions(),
                'statuses' => ShiftSchedule::statuses(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/ShiftSchedules/Create', [
            'options' => [
                'employees' => $this->employeeOptions(),
                'shifts' => $this->shiftOptions(),
                'statuses' => ShiftSchedule::statuses(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSchedule($request);
        $data['created_by'] = Auth::id();

        $schedule = ShiftSchedule::create($data);

        return redirect()
            ->route('hr.shift-schedules.index')
            ->with('success', 'Shift schedule has been created.');
    }

    public function edit(ShiftSchedule $shiftSchedule): Response
    {
        $shiftSchedule->load(['user:id,name,employee_id', 'shift:id,name,color,start_time,end_time']);

        return Inertia::render('HR/ShiftSchedules/Edit', [
            'schedule' => $this->toArray($shiftSchedule),
            'options' => [
                'employees' => $this->employeeOptions(),
                'shifts' => $this->shiftOptions(),
                'statuses' => ShiftSchedule::statuses(),
            ],
        ]);
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
            'user_id' => ['required', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'work_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', Rule::in(ShiftSchedule::statuses())],
            'notes' => ['nullable', 'string'],
        ] + [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('shift_schedules')
                    ->where(fn ($query) => $query
                        ->where('shift_id', $request->input('shift_id'))
                        ->whereDate('work_date', $request->input('work_date')))
                    ->ignore($schedule?->id),
            ],
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
                'value' => $user->id,
                'label' => trim("{$user->name}".($user->employee_id ? " ({$user->employee_id})" : '')),
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
                'value' => $shift->id,
                'label' => $shift->name,
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
            'user_id' => $schedule->user_id,
            'shift_id' => $schedule->shift_id,
            'work_date' => $schedule->work_date?->format('Y-m-d'),
            'start_time' => $time($schedule->start_time),
            'end_time' => $time($schedule->end_time),
            'status' => $schedule->status,
            'notes' => $schedule->notes,
            'employee_name' => $schedule->user?->name,
            'employee_code' => $schedule->user?->employee_id,
            'shift_name' => $schedule->shift?->name,
            'shift_color' => $schedule->shift?->color,
            'shift_start_time' => $time($schedule->shift?->start_time),
            'shift_end_time' => $time($schedule->shift?->end_time),
        ];
    }
}
