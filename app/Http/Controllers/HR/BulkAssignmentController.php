<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\BulkAssignment\BulkAssignmentRequest;
use App\Services\HR\BulkAssignmentService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BulkAssignmentController extends Controller
{
    public function __construct(
        protected BulkAssignmentService $service
    ) {}

    public function index(): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $filters = request()->only([
            'company',
            'branch',
            'division',
            'department',
            'designation',
            'employment_type',
            'status',
            'employee_id',
            'search',
        ]);

        $employees = $this->service->filterEmployees($filters, 50);

        return Inertia::render('HR/BulkAssignments/Index', [
            'employees' => $employees,
            'filters' => $filters,
            'options' => $this->service->filterOptions(),
        ]);
    }

    public function store(BulkAssignmentRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $result = $this->service->processAssignment(
            $request->employee_ids,
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('hr.bulk-assignments.index')
            ->with('bulk_assignment_summary', $result->toArray());
    }
}
