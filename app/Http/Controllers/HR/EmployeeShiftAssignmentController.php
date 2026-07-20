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
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignments = EmployeeShiftAssignment::query()
            ->with(['shift', 'creator', 'employee'])
            ->latest('effective_from')
            ->paginate(15);

        return Inertia::render('HR/EmployeeShiftAssignments/Index', [
            'assignments' => $assignments,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );
        
        $employees = User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get();
        
        $shifts = Shift::query()
                ->where('status', true)
                ->orderBy('shift_name')
                ->get(['id', 'shift_name', 'shift_code']);
// dd($shifts);
        return Inertia::render('HR/EmployeeShiftAssignments/Create', [
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(
        StoreEmployeeShiftAssignmentRequest $request
    ): RedirectResponse {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = User::findOrFail($request->employee_id);

        $this->service->store(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.shift-assignments.index')
            ->with('success', 'Shift assigned successfully.');
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(EmployeeShiftAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignment->load(['employee', 'shift']);

        return Inertia::render('HR/EmployeeShiftAssignments/Edit', [
            'assignment' => $assignment,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),

            'shifts' => Shift::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(
        UpdateEmployeeShiftAssignmentRequest $request,
        EmployeeShiftAssignment $assignment
    ): RedirectResponse {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = User::findOrFail($request->employee_id);

        $this->service->update(
            $assignment,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.shift-assignments.index')
            ->with('success', 'Shift assignment updated successfully.');
    }

    /**
     * Show the form for changing the shift assignment.
     */
    public function change(EmployeeShiftAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignment->load(['employee', 'shift']);

        $data =  [
            'currentAssignment' => $assignment,
            'employee' => $assignment->employee,
            'shifts' => $this->service->getAvailableShifts($assignment->employee),
        ];
        // dd($data);
        return Inertia::render('HR/EmployeeShiftAssignments/Change', $data);
    }

    /**
     * Store a new Shift Assignment by closing the current assignment.
     */
    public function storeChange(
        StoreEmployeeShiftAssignmentRequest $request,
        EmployeeShiftAssignment $assignment
    ): RedirectResponse {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $this->service->changeAssignment(
            $assignment,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.shift-assignments.index')
            ->with('success', 'Shift assignment changed successfully.');
    }

    /**
     * Display assignment history for an employee.
     */
    public function history(EmployeeShiftAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = $assignment->employee;

        $history = EmployeeShiftAssignment::query()
            ->with(['shift', 'creator'])
            ->where('user_id', $employee->id)
            ->orderBy('effective_from')
            ->get();

        return Inertia::render('HR/EmployeeShiftAssignments/History', [
            'employee' => $employee,
            'history' => $history,
        ]);
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(EmployeeShiftAssignment $assignment): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $this->service->delete($assignment);

        return back()->with('success', 'Assignment removed successfully.');
    }
}
