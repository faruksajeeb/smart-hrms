<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeReportingManagerAssignment\StoreReportingManagerAssignmentRequest;
use App\Models\EmployeeReportingManagerAssignment;
use App\Models\User;
use App\Services\HR\EmployeeReportingManagerAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeReportingManagerAssignmentController extends Controller
{
    public function __construct(
        protected EmployeeReportingManagerAssignmentService $service
    ) {}

    /**
     * Display a listing of reporting manager assignments.
     */
    public function index(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employeeId = $request->integer('employee_id');
        $managerId = $request->integer('manager_id');

        $assignments = EmployeeReportingManagerAssignment::query()
            ->with(['manager', 'creator', 'employee'])
            ->when($employeeId, fn ($query) => $query->where('user_id', $employeeId))
            ->when($managerId, fn ($query) => $query->where('manager_id', $managerId))
            ->latest('effective_from')
            ->paginate(15)
            ->withQueryString();

        $employees = User::query()
            ->select('id', 'employee_id', 'name')
            ->orderBy('employee_id')
            ->get();

        $managers = User::query()
            ->select('id', 'employee_id', 'name')
            ->orderBy('employee_id')
            ->get();

        return Inertia::render('HR/EmployeeReportingManagerAssignments/Index', [
            'assignments' => $assignments,
            'employees' => $employees,
            'managers' => $managers,
            'filters' => [
                'employee_id' => $employeeId,
                'manager_id' => $managerId,
            ],
        ]);
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employees = User::query()
            ->select('id', 'employee_id', 'name')
            ->orderBy('employee_id')
            ->get();

        $managers = User::query()
            ->select('id', 'employee_id', 'name')
            ->orderBy('employee_id')
            ->get();

        $preselectedEmployee = null;

        if ($request->filled('employee_id')) {
            $preselectedEmployee = User::query()
                ->select('id', 'employee_id', 'name')
                ->find($request->integer('employee_id'));
        }

        return Inertia::render('HR/EmployeeReportingManagerAssignments/Create', [
            'employees' => $employees,
            'managers' => $managers,
            'preselectedEmployee' => $preselectedEmployee,
        ]);
    }

    /**
     * Store a newly created assignment.
     */
    public function store(
        StoreReportingManagerAssignmentRequest $request
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
            ->route('hr.reporting-manager-assignments.index')
            ->with('success', 'Reporting manager assigned successfully.');
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(EmployeeReportingManagerAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignment->load(['employee', 'manager']);

        return Inertia::render('HR/EmployeeReportingManagerAssignments/Edit', [
            'assignment' => $assignment,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
            'managers' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
        ]);
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(
        StoreReportingManagerAssignmentRequest $request,
        EmployeeReportingManagerAssignment $assignment
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
            ->route('hr.reporting-manager-assignments.index')
            ->with('success', 'Reporting manager assignment updated successfully.');
    }

    /**
     * Show the form for changing the reporting manager.
     */
    public function change(EmployeeReportingManagerAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignment->load(['employee', 'manager']);

        $managers = User::query()
            ->select('id', 'employee_id', 'name')
            ->where('id', '!=', $assignment->user_id)
            ->orderBy('employee_id')
            ->get();

        return Inertia::render('HR/EmployeeReportingManagerAssignments/Change', [
            'currentAssignment' => $assignment,
            'employee' => $assignment->employee,
            'managers' => $managers,
        ]);
    }

    /**
     * Store a new Reporting Manager Assignment by closing the current assignment.
     */
    public function storeChange(
        StoreReportingManagerAssignmentRequest $request,
        EmployeeReportingManagerAssignment $assignment
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
            ->route('hr.reporting-manager-assignments.index')
            ->with('success', 'Reporting manager changed successfully.');
    }

    /**
     * Display assignment history for an employee.
     */
    public function history(EmployeeReportingManagerAssignment $assignment): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = $assignment->employee;

        $history = EmployeeReportingManagerAssignment::query()
            ->with(['manager', 'creator'])
            ->where('user_id', $employee->id)
            ->orderBy('effective_from')
            ->get();

        return Inertia::render('HR/EmployeeReportingManagerAssignments/History', [
            'employee' => $employee,
            'history' => $history,
        ]);
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(EmployeeReportingManagerAssignment $assignment): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $this->service->delete($assignment);

        return back()->with('success', 'Assignment removed successfully.');
    }
}
