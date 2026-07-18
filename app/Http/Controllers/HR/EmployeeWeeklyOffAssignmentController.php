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
     * Show the form for editing the specified assignment.
     */
    public function edit(
        EmployeeWeeklyOffAssignment $assignment
    ): Response {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $employees = User::where('id', '!=', 0)->pluck('name', 'id')->all();
        $policies = \App\Models\WeeklyOffPolicy::where('status', true)
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        return Inertia::render('HR/EmployeeWeeklyOffAssignments/Edit', [
            'assignment' => $assignment,
            'employees' => $employees,
            'policies' => $policies,
        ]);
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(
        UpdateEmployeeWeeklyOffAssignmentRequest $request,
        EmployeeWeeklyOffAssignment $assignment
    ): RedirectResponse {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $employee = User::findOrFail($request->employee_id);

        $this->service->update(
            $assignment,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.weekly-off-assignments.index')
            ->with('success', 'Weekly off assignment updated successfully.');
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