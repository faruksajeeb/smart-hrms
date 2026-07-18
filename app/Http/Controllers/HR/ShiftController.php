<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('HR/Shifts/Index', [
            'shifts' => Shift::orderBy('shift_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage attendance'), 403);

        $data = $this->validateData($request);

        $data['company_id'] = auth()->user()->company_id ?? 1;
        $data['created_by'] = auth()->id();

        Shift::create($data);

        return back()->with('success', 'Shift created successfully.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        abort_unless($request->user()->can('manage attendance'), 403);

        $data = $this->validateData($request);

        $data['updated_by'] = auth()->id();

        $shift->update($data);

        return back()->with('success', 'Shift updated successfully.');
    }

    public function destroy(Request $request, Shift $shift): RedirectResponse
    {
        abort_unless($request->user()->can('manage attendance'), 403);

        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }

    /**
     * Validate Shift Data
     */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'shift_name' => ['required', 'string', 'max:100'],
            'shift_code' => ['required', 'string', 'max:20'],

            'description' => ['nullable', 'string'],

            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],

            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i'],

            'grace_time' => ['required', 'integer', 'min:0'],
            'working_hours' => ['required', 'numeric', 'min:0'],

            'late_after' => ['required', 'integer', 'min:0'],
            'half_day_after' => ['required', 'integer', 'min:0'],

            'minimum_work_hours' => ['required', 'numeric', 'min:0'],

            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],

            'status' => ['boolean'],
            'is_flexible' => ['boolean'],
            'is_night_shift' => ['boolean'],
        ]);

        // Handle checkboxes
        $data['status'] = $request->boolean('status');
        $data['is_flexible'] = $request->boolean('is_flexible');
        $data['is_night_shift'] = $request->boolean('is_night_shift');

        return $data;
    }
}