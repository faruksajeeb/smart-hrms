<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(): Response
    {
        $shifts = Shift::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Shift $shift) => $this->toArray($shift));

        return Inertia::render('HR/Shifts/Index', [
            'shifts' => $shifts,
            'stats' => [
                'total' => $shifts->count(),
                'active' => $shifts->where('is_active', true)->count(),
                'inactive' => $shifts->where('is_active', false)->count(),
                'overnight' => $shifts->where('is_overnight', true)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/Shifts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateShift($request);

        $shift = Shift::create($data);

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', "{$shift->name} shift has been created.");
    }

    public function edit(Shift $shift): Response
    {
        return Inertia::render('HR/Shifts/Edit', [
            'shift' => $this->toArray($shift),
        ]);
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $shift->update($this->validateShift($request));

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', "{$shift->name} shift has been updated.");
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $name = $shift->name;
        $shift->delete();

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', "{$name} shift has been deleted.");
    }

    public function toggleStatus(Shift $shift): RedirectResponse
    {
        $shift->update([
            'is_active' => ! $shift->is_active,
        ]);

        $status = $shift->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('hr.shifts.index')
            ->with('success', "{$shift->name} shift has been {$status}.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateShift(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'is_overnight' => ['boolean'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['boolean'],
        ]);

        $data['is_overnight'] = $request->boolean('is_overnight');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function toArray(Shift $shift): array
    {
        $time = fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null;

        return [
            'id' => $shift->id,
            'name' => $shift->name,
            'start_time' => $time($shift->start_time),
            'end_time' => $time($shift->end_time),
            'is_overnight' => $shift->is_overnight,
            'color' => $shift->color,
            'is_active' => $shift->is_active,
        ];
    }
}
