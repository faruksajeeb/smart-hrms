<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Services\HR\Leave\LeaveReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveReportController extends Controller
{
    public function __construct(protected LeaveReportService $reports) {}

    public function index(Request $request): Response
    {
        return Inertia::render('HR/Leave/Reports/Index', [
            'report' => $request->string('report', 'dashboard')->toString(),
            'filters' => $request->all(),
            'options' => $this->reports->options($request->user()),
            'cards' => $this->reports->dashboard($request->user()),
        ]);
    }

    public function data(Request $request)
    {
        $type = $request->string('report', 'balance')->toString();
        abort_unless(in_array($type, ['balance', 'ledger', 'history', 'department', 'monthly', 'utilization'], true), 404);
        $this->authorizeReport($request, $type);
        return response()->json($this->reports->report($request->user(), $type, $this->validated($request)));
    }

    public function export(Request $request)
    {
        $type = $request->string('report', 'balance')->toString();
        abort_unless(in_array($type, ['balance', 'ledger', 'history', 'department', 'monthly', 'utilization'], true), 404);
        $this->authorizeReport($request, $type);
        [$columns, $rows] = $this->reports->export($request->user(), $type, $this->validated($request));
        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            foreach ($rows as $row) fputcsv($handle, array_values((array) $row));
            fclose($handle);
        }, "leave-{$type}-report.csv", ['Content-Type' => 'text/csv']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'report' => ['required', 'in:balance,ledger,history,department,monthly,utilization'],
            'employee_id' => ['nullable', 'integer'], 'employee' => ['nullable', 'string', 'max:100'],
            'leave_type_id' => ['nullable', 'integer'], 'leave_policy_id' => ['nullable', 'integer'],
            'company_id' => ['nullable', 'integer'], 'branch_id' => ['nullable', 'integer'],
            'division_id' => ['nullable', 'integer'], 'department_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'], 'unit_id' => ['nullable', 'integer'], 'designation_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'], 'as_of_date' => ['nullable', 'date'],
            'year' => ['nullable', 'integer', 'between:1970,2100'], 'month' => ['nullable', 'integer', 'between:1,12'],
            'status' => ['nullable', 'string'], 'transaction_type' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'], 'per_page' => ['nullable', 'integer', 'between:10,100'],
        ]);
    }

    protected function authorizeReport(Request $request, string $type): void
    {
        abort_unless($request->user()->hasRole(\App\Models\User::ROLE_ADMIN)
            || $request->user()->hasAnyPermission(['leave.reports.view', "leave.reports.{$type}"]), 403);
    }
}
