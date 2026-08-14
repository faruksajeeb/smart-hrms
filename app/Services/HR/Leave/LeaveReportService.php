<?php

namespace App\Services\HR\Leave;

use App\Models\LeaveBalanceLedger;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LeaveReportService
{
    public function dashboard(User $actor): array
    {
        return [
            ['key' => 'balance', 'title' => 'Leave Balance', 'description' => 'Ledger-derived balances and historical as-of reporting.'],
            ['key' => 'ledger', 'title' => 'Leave Ledger', 'description' => 'Complete credit, debit, and balance transaction history.'],
            ['key' => 'history', 'title' => 'Employee Leave History', 'description' => 'Applications, statuses, day breakdowns, and approval dates.'],
            ['key' => 'department', 'title' => 'Department-wise Leave', 'description' => 'Usage and application aggregation by organization.'],
            ['key' => 'monthly', 'title' => 'Monthly Leave', 'description' => 'Month-by-month approved leave analysis and trend.'],
            ['key' => 'utilization', 'title' => 'Leave Utilization', 'description' => 'Available versus used leave by type and department.'],
        ];
    }

    public function report(User $actor, string $type, array $filters): array
    {
        return match ($type) {
            'balance' => $this->balance($actor, $filters),
            'ledger' => $this->ledger($actor, $filters),
            'history' => $this->history($actor, $filters),
            'department' => $this->department($actor, $filters),
            'monthly' => $this->monthly($actor, $filters),
            'utilization' => $this->utilization($actor, $filters),
            default => ['rows' => [], 'summary' => [], 'meta' => []],
        };
    }

    public function export(User $actor, string $type, array $filters): array
    {
        $data = $this->report($actor, $type, array_merge($filters, ['per_page' => 100000]));
        return [$data['columns'] ?? [], $data['rows'] ?? []];
    }

    public function options(User $actor): array
    {
        $item = fn (string $category) => DB::table('master_data_items')->where('category', $category)->where('status', 'active')
            ->when(!$actor->hasRole(User::ROLE_ADMIN) && $category === 'company', fn ($q) => $q->where('id', $actor->company_id))
            ->when(!$actor->hasRole(User::ROLE_ADMIN) && $category === 'branch', fn ($q) => $q->where('id', $actor->branch_id))
            ->orderBy('name')->get(['id', 'parent_id', 'name', 'code']);

        return [
            'companies' => $item('company'), 'branches' => $item('branch'), 'divisions' => $item('division'),
            'departments' => $item('department'), 'sections' => $item('section'), 'units' => $item('unit'),
            'designations' => $item('designation'), 'employmentTypes' => $item('employee_type'),
            'leaveTypes' => LeaveType::where('status', 'active')->orderBy('leave_name')->get(['id', 'leave_name', 'leave_code']),
        ];
    }

    protected function ledgerBase(User $actor, array $filters): Builder
    {
        $q = DB::table('leave_balance_ledgers as l')
            ->join('users as u', 'u.id', '=', 'l.user_id')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'l.leave_type_id')
            ->leftJoin('leave_policies as lp', 'lp.id', '=', 'l.leave_policy_id')
            ->leftJoin('users as performer', 'performer.id', '=', 'l.performed_by')
            ->leftJoin('users as approver', 'approver.id', '=', 'l.approved_by')
            ->leftJoin('users as processor', 'processor.id', '=', 'l.processed_by')
            ->select('l.*', 'u.name as employee_name', 'u.employee_id as employee_code', 'lt.leave_name', 'lt.leave_code', 'lp.policy_name', 'performer.name as performed_by_name', 'approver.name as approved_by_name', 'processor.name as processed_by_name');
        return $this->applyEmployeeFilters($q, $actor, $filters, 'u', 'l');
    }

    protected function applyEmployeeFilters(Builder $q, User $actor, array $filters, string $userAlias = 'u', string $transactionAlias = 'l'): Builder
    {
        $orgAlias = $transactionAlias === 'l' ? $transactionAlias : $userAlias;
        if (!$actor->hasRole(User::ROLE_ADMIN)) {
            $actor->company_id ? $q->where("{$orgAlias}.company_id", $actor->company_id) : $q->whereNull("{$orgAlias}.company_id");
            if ($actor->branch_id) $q->where("{$orgAlias}.branch_id", $actor->branch_id);
        }
        if ($transactionAlias === 'l') {
            foreach (['company_id', 'branch_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'designation_id'] as $field) {
                if (!empty($filters[$field])) $q->where("{$transactionAlias}.{$field}", $filters[$field]);
            }
        }
        if (!empty($filters['company_id'])) $q->where("{$orgAlias}.company_id", $filters['company_id']);
        if (!empty($filters['branch_id'])) $q->where("{$orgAlias}.branch_id", $filters['branch_id']);
        foreach (['division_id', 'department_id', 'section_id', 'unit_id', 'designation_id'] as $field) {
            if (!empty($filters[$field])) $q->where("{$orgAlias}.{$field}", $filters[$field]);
        }
        if (!empty($filters['employee_id'])) $q->where("{$userAlias}.id", $filters['employee_id']);
        if (!empty($filters['employee'])) $q->where(fn ($s) => $s->where("{$userAlias}.name", 'like', "%{$filters['employee']}%")->orWhere("{$userAlias}.employee_id", 'like', "%{$filters['employee']}%"));
        if (!empty($filters['leave_type_id'])) $q->where("{$transactionAlias}.leave_type_id", $filters['leave_type_id']);
        if (!empty($filters['leave_policy_id'])) $q->where("{$transactionAlias}.leave_policy_id", $filters['leave_policy_id']);
        return $q;
    }

    protected function dateFilter(Builder $q, array $filters, string $column): Builder
    {
        if (!empty($filters['from'])) $q->whereDate($column, '>=', $filters['from']);
        if (!empty($filters['to'])) $q->whereDate($column, '<=', $filters['to']);
        if (!empty($filters['year'])) $q->whereYear($column, (int) $filters['year']);
        if (!empty($filters['month'])) $q->whereMonth($column, (int) $filters['month']);
        return $q;
    }

    protected function balance(User $actor, array $filters): array
    {
        $asOf = $filters['as_of_date'] ?? now()->toDateString();
        $q = $this->ledgerBase($actor, $filters)->whereDate('l.transaction_date', '<=', $asOf);
        $rows = $q->orderBy('l.user_id')->orderBy('l.leave_type_id')->orderByDesc('l.transaction_date')->orderByDesc('l.id')->get()->groupBy(fn ($r) => $r->user_id . ':' . $r->leave_type_id)->map(function ($items) {
            $latest = $items->first();
            return [
                'employee_id' => $latest->employee_code, 'employee_name' => $latest->employee_name, 'leave_type' => $latest->leave_name, 'leave_code' => $latest->leave_code, 'policy' => $latest->policy_name,
                'opening' => round($items->where('transaction_type', 'opening')->sum('credit_days'), 2), 'accrued' => round($items->where('transaction_type', 'accrual')->sum('credit_days'), 2), 'carry_forward' => round($items->where('transaction_type', 'carry_forward')->sum('credit_days'), 2),
                'used' => round($items->where('transaction_type', 'leave_approved')->sum('debit_days'), 2), 'cancelled' => round($items->where('transaction_type', 'leave_cancelled')->sum('credit_days'), 2), 'adjustment' => round($items->where('transaction_type', 'adjustment')->sum('days'), 2), 'encashment' => round($items->where('transaction_type', 'encashment')->sum('debit_days'), 2), 'expiry' => round($items->where('transaction_type', 'expiry')->sum('debit_days'), 2), 'current_balance' => (float) $latest->balance_after,
                'employee_user_id' => $latest->user_id, 'leave_type_id' => $latest->leave_type_id,
            ];
        })->values();
        return ['rows' => $rows->forPage((int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 25))->values(), 'summary' => ['records' => $rows->count(), 'as_of_date' => $asOf], 'meta' => ['current_page' => (int) ($filters['page'] ?? 1), 'per_page' => (int) ($filters['per_page'] ?? 25), 'last_page' => max(1, (int) ceil($rows->count() / (int) ($filters['per_page'] ?? 25)))], 'columns' => ['Employee ID', 'Employee Name', 'Leave Type', 'Policy', 'Opening', 'Accrued', 'Carry Forward', 'Used', 'Cancelled', 'Adjustment', 'Encashment', 'Expiry', 'Current Balance']];
    }

    protected function ledger(User $actor, array $filters): array
    {
        $q = $this->dateFilter($this->ledgerBase($actor, $filters), $filters, 'l.transaction_date');
        if (!empty($filters['transaction_type'])) $q->where('l.transaction_type', $filters['transaction_type']);
        $total = (clone $q)->count('l.id');
        $rows = $q->orderByDesc('l.transaction_date')->orderByDesc('l.id')->forPage(((int) ($filters['page'] ?? 1) - 1) * (int) ($filters['per_page'] ?? 25), (int) ($filters['per_page'] ?? 25))->get()->map(fn ($r) => (array) $r);
        return ['rows' => $rows, 'summary' => ['records' => $total], 'meta' => $this->meta($total, $filters), 'columns' => ['Transaction Date', 'Effective Date', 'Employee ID', 'Employee Name', 'Leave Type', 'Policy', 'Transaction Type', 'Source', 'Reference', 'Credit Days', 'Debit Days', 'Balance After', 'Remarks', 'Performed By', 'Approved By', 'Processed By']];
    }

    protected function history(User $actor, array $filters): array
    {
        $days = DB::table('leave_application_days')->select('leave_application_id', DB::raw('SUM(leave_days) as leave_days'))->where('counts_as_leave', true)->groupBy('leave_application_id');
        $q = DB::table('leave_applications as a')->join('users as u', 'u.id', '=', 'a.user_id')->leftJoin('leave_types as lt', 'lt.id', '=', 'a.leave_type_id')->leftJoin('leave_policies as lp', 'lp.id', '=', 'a.leave_policy_id')->leftJoin('users as d', 'd.id', '=', 'a.delegate_user_id')->leftJoinSub($days, 'day_totals', 'day_totals.leave_application_id', '=', 'a.id')->select('a.*', 'u.name as employee_name', 'u.employee_id as employee_code', 'lt.leave_name', 'lt.leave_code', 'lp.policy_name', 'd.name as delegate_name', DB::raw('COALESCE(day_totals.leave_days, 0) as days'));
        $q = $this->applyEmployeeFilters($q, $actor, $filters, 'u', 'a');
        $q = $this->dateFilter($q, $filters, 'a.start_date');
        if (!empty($filters['status'])) $q->where('a.status', $filters['status']);
        $total = (clone $q)->count('a.id');
        $rows = $q->orderByDesc('a.start_date')->forPage(((int) ($filters['page'] ?? 1) - 1) * (int) ($filters['per_page'] ?? 25), (int) ($filters['per_page'] ?? 25))->get()->map(fn ($r) => (array) $r);
        return ['rows' => $rows, 'summary' => ['records' => $total], 'meta' => $this->meta($total, $filters), 'columns' => ['Application No', 'Employee ID', 'Employee Name', 'Leave Type', 'Policy', 'Start Date', 'End Date', 'Total Days', 'Requested Days', 'Leave Days', 'Status', 'Reason', 'Delegate', 'Submitted At', 'Approved At', 'Rejected At', 'Cancelled At', 'Withdrawn At']];
    }

    protected function department(User $actor, array $filters): array
    {
        $q = DB::table('leave_application_days as d')->join('leave_applications as a', 'a.id', '=', 'd.leave_application_id')->join('users as u', 'u.id', '=', 'a.user_id')->leftJoin('master_data_items as dept', 'dept.id', '=', 'u.department_id')->select('u.department_id', 'dept.name as department', DB::raw('COUNT(DISTINCT u.id) as employee_count'), DB::raw('COUNT(DISTINCT a.id) as applications'), DB::raw("COUNT(DISTINCT CASE WHEN a.status = 'approved' THEN a.id END) as approved_applications"), DB::raw("SUM(CASE WHEN a.status = 'approved' AND d.counts_as_leave = 1 THEN d.leave_days ELSE 0 END) as total_leave_days"));
        $q = $this->applyEmployeeFilters($q, $actor, $filters, 'u', 'a');
        $q = $this->dateFilter($q, $filters, 'd.leave_date')->groupBy('u.department_id', 'dept.name')->orderBy('dept.name');
        $rows = $q->get()->map(fn ($r) => (array) $r);
        return ['rows' => $rows, 'summary' => ['departments' => $rows->count(), 'total_leave_days' => round($rows->sum('total_leave_days'), 2)], 'meta' => [], 'columns' => ['Department', 'Employee Count', 'Applications', 'Approved Applications', 'Total Leave Days']];
    }

    protected function monthly(User $actor, array $filters): array
    {
        $q = DB::table('leave_application_days as d')->join('leave_applications as a', 'a.id', '=', 'd.leave_application_id')->join('users as u', 'u.id', '=', 'a.user_id')->leftJoin('leave_types as lt', 'lt.id', '=', 'a.leave_type_id')->select(DB::raw('YEAR(d.leave_date) as year'), DB::raw('MONTH(d.leave_date) as month'), 'a.leave_type_id', 'lt.leave_name', DB::raw('COUNT(DISTINCT a.id) as applications'), DB::raw("SUM(CASE WHEN a.status = 'approved' AND d.counts_as_leave = 1 THEN d.leave_days ELSE 0 END) as approved_days"), DB::raw("SUM(CASE WHEN a.status = 'rejected' THEN d.leave_days ELSE 0 END) as rejected_days"), DB::raw("SUM(CASE WHEN a.status = 'cancelled' THEN d.leave_days ELSE 0 END) as cancelled_days"));
        $q = $this->applyEmployeeFilters($q, $actor, $filters, 'u', 'a');
        $q = $this->dateFilter($q, $filters, 'd.leave_date')->groupBy(DB::raw('YEAR(d.leave_date)'), DB::raw('MONTH(d.leave_date)'), 'a.leave_type_id', 'lt.leave_name')->orderBy('year')->orderBy('month')->orderBy('lt.leave_name');
        $rows = $q->get()->map(fn ($r) => (array) $r);
        return ['rows' => $rows, 'summary' => ['approved_days' => round($rows->sum('approved_days'), 2), 'applications' => $rows->sum('applications')], 'meta' => [], 'columns' => ['Year', 'Month', 'Leave Type', 'Applications', 'Approved Days', 'Rejected Days', 'Cancelled Days']];
    }

    protected function utilization(User $actor, array $filters): array
    {
        $data = $this->balance($actor, array_merge($filters, ['per_page' => 100000]))['rows'];
        $rows = collect($data)->groupBy('leave_type')->map(fn ($items, $type) => ['leave_type' => $type, 'available' => round($items->sum(fn ($r) => $r['opening'] + $r['accrued'] + $r['carry_forward'] + $r['adjustment']), 2), 'used' => round($items->sum('used'), 2), 'remaining' => round($items->sum('current_balance'), 2), 'utilization' => round(($items->sum('used') / max($items->sum(fn ($r) => $r['opening'] + $r['accrued'] + $r['carry_forward'] + $r['adjustment']), 0.01)) * 100, 2)])->values();
        return ['rows' => $rows, 'summary' => ['available' => round($rows->sum('available'), 2), 'used' => round($rows->sum('used'), 2)], 'meta' => [], 'columns' => ['Leave Type', 'Available', 'Used', 'Remaining', 'Utilization %']];
    }

    protected function meta(int $total, array $filters): array { $per = (int) ($filters['per_page'] ?? 25); return ['current_page' => (int) ($filters['page'] ?? 1), 'per_page' => $per, 'total' => $total, 'last_page' => max(1, (int) ceil($total / $per))]; }
}
