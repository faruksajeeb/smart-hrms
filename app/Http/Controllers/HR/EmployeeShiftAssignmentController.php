<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeShiftAssignment\StoreEmployeeShiftAssignmentRequest;
use App\Http\Requests\HR\EmployeeShiftAssignment\UpdateEmployeeShiftAssignmentRequest;
use App\Models\EmployeeShiftAssignment;
use App\Models\Shift;
use App\Models\User;
use App\Services\HR\EmployeeShiftAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeShiftAssignmentController extends Controller
{
    public function __construct(
        protected EmployeeShiftAssignmentService $service
    ) {}

    /**
     * Display a listing of shift assignments.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', EmployeeShiftAssignment::class);

        $assignments = EmployeeShiftAssignment::query()
            ->with(['shift', 'creator', 'employee'])
            ->latest('effective_from')
            ->paginate(15);

        return Inertia::render('HR/EmployeeShiftAssignments/Index', [
            'assignments' => $assignments,
            'employees' => User::where('id', '!=', 0)->pluck('name', 'id')->all(),
        ]);
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(): Response
    {
        $this->authorize('create', EmployeeShiftAssignment::class);

        $employees = User::where('id', '!=', 0)->pluck('name', 'id')->all();
        $shifts = $this->service->getAvailableShifts(new User()); // We'll fix this below

        // Get shifts directly from the model since the service doesn't really need the employee
        $shifts = Shift::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('HR/EmployeeShiftAssignments/Create', [
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(
        EmployeeShiftAssignmentRequest $request
    ): RedirectResponse {
        $this->authorize('create', EmployeeShiftAssignment::class);

        $employee = User::findOrFail($request->employee_id);

        $this->service->store(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('shift-assignments.index')
            ->with('success', 'Shift assigned successfully.');
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(
        EmployeeShiftAssignment $assignment
    ): Response {
        $this->authorize('update', $assignment);

        $employees = User::where('id', '!=', 0)->pluck('name', 'id')->all();
        $shifts = $this->service->getAvailableShifts(new User()); // Again, we'll fix

        $shifts = Shift::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('HR/EmployeeShiftAssignments/Edit', [
            'assignment' => $assignment,
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(
        EmployeeShiftAssignmentRequest $request,
        EmployeeShiftAssignment $assignment
    ): RedirectResponse {
        $this->authorize('update', $assignment);

        $employee = User::findOrFail($request->employee_id);

        $this->service->update(
            $assignment,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('shift-assignments.index')
            ->with('success', 'Shift assignment updated successfully.');
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(
        EmployeeShiftAssignment $assignment
    ): RedirectResponse {
        $this->authorize('delete', $assignment);

        $this->service->delete($assignment);

        return back()->with('success', 'Shift assignment removed successfully.');
    }
}