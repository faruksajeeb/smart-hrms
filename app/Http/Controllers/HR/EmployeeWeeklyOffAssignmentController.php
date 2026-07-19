<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeWeeklyOffAssignment\StoreEmployeeWeeklyOffAssignmentRequest;
use App\Http\Requests\HR\EmployeeWeeklyOffAssignment\UpdateEmployeeWeeklyOffAssignmentRequest;
use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\User;
use App\Models\WeeklyOffPolicy;
use App\Services\HR\EmployeeWeeklyOffAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeWeeklyOffAssignmentController extends Controller
{
    public function __construct(
        protected EmployeeWeeklyOffAssignmentService $service
    ) {}

    /**
     * Display a listing of weekly off assignments.
     */
    public function index(): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $assignments = EmployeeWeeklyOffAssignment::query()
            ->with(['weeklyOffPolicy', 'creator', 'employee'])
            ->latest('effective_from')
            ->paginate(15);

        return Inertia::render('HR/EmployeeWeeklyOffAssignments/Index', [
            'assignments' => $assignments,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        return Inertia::render(
            'HR/EmployeeWeeklyOffAssignments/Create',
            [

                'employees' => User::query()
                    ->select('id', 'employee_id', 'name')
                    ->orderBy('employee_id')
                    ->get(),

                'policies' => WeeklyOffPolicy::query()
                    ->where('status', true)
                    ->orderBy('policy_name')
                    ->get([
                        'id',
                        'policy_name',
                        'policy_code',
                    ]),

            ]
        );
    }
    /**
     * Store a newly created assignment.
     */
    public function store(
        StoreEmployeeWeeklyOffAssignmentRequest $request
    ): RedirectResponse {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $employee = User::findOrFail($request->employee_id);

        $this->service->store(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.weekly-off-assignments.index')
            ->with('success', 'Weekly off assigned successfully.');
    }

    /**
     * Show the Change Weekly Off Assignment form.
     */
    public function change(
        EmployeeWeeklyOffAssignment $assignment
    ): Response {

        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $assignment->load([
            'employee',
            'weeklyOffPolicy',
        ]);

        return Inertia::render(
            'HR/EmployeeWeeklyOffAssignments/Change',
            [

                // Current assignment (read-only)
                'currentAssignment' => $assignment,

                // Employee (read-only)
                'employee' => $assignment->employee,

                // Available policies
                'policies' => $this->service
                    ->getAvailablePolicies(
                        $assignment->employee
                    ),

            ]
        );
    }

    /**
     * Store a new Weekly Off Assignment by closing the current assignment.
     */
    public function storeChange(
        StoreEmployeeWeeklyOffAssignmentRequest $request,
        EmployeeWeeklyOffAssignment $assignment
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
            ->route('hr.weekly-off-assignments.index')
            ->with(
                'success',
                'Weekly off assignment changed successfully.'
            );
    }

    /**
     * Display assignment history for an employee.
     */
    public function history(
        EmployeeWeeklyOffAssignment $assignment
    ): Response {

        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = $assignment->employee;

        $history = EmployeeWeeklyOffAssignment::query()

            ->with([
                'weeklyOffPolicy',
                'creator',
            ])

            ->where('user_id', $employee->id)

            ->orderBy('effective_from')

            ->get();

        return Inertia::render(
            'HR/EmployeeWeeklyOffAssignments/History',
            [

                'employee' => $employee,

                'history' => $history,

            ]
        );
    }

    /**
     * Remove the specified assignment from storage.
     */
    public function destroy(
        EmployeeWeeklyOffAssignment $assignment
    ): RedirectResponse {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $this->service->delete($assignment);

        return back()->with('success', 'Assignment removed successfully.');
    }
}
