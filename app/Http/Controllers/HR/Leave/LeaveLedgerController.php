<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalanceLedger;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveLedgerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = LeaveBalanceLedger::query()->with(['employee', 'leaveType']);

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->integer('employee_id'));
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->integer('leave_type_id'));
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->string('transaction_type'));
        }

        $ledgers = $query->orderBy('transaction_date', 'desc')->paginate(15);

        $employees = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $transactionTypes = [
            ['value' => 'opening', 'label' => 'Opening'],
            ['value' => 'accrual', 'label' => 'Accrual'],
            ['value' => 'carry_forward', 'label' => 'Carry Forward'],
            ['value' => 'leave_approved', 'label' => 'Leave Approved'],
            ['value' => 'leave_cancelled', 'label' => 'Leave Cancelled'],
            ['value' => 'adjustment', 'label' => 'Adjustment'],
            ['value' => 'encashment', 'label' => 'Encashment'],
            ['value' => 'expiry', 'label' => 'Expiry'],
        ];

        return Inertia::render('HR/Leave/LeaveLedgers/Index', [
            'ledgers' => $ledgers,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'transactionTypes' => $transactionTypes,
            'filters' => [
                'employee_id' => $request->integer('employee_id'),
                'leave_type_id' => $request->integer('leave_type_id'),
                'transaction_type' => $request->string('transaction_type'),
            ],
        ]);
    }

    public function summary(Request $request): Response
    {
        $employees = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $selectedEmployeeId = $request->integer('employee_id');
        $selectedLeaveTypeId = $request->integer('leave_type_id');

        $summary = null;

        if ($selectedEmployeeId && $selectedLeaveTypeId) {
            $employee = User::find($selectedEmployeeId);
            $leaveType = LeaveType::find($selectedLeaveTypeId);

            if ($employee && $leaveType) {
                $opening = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('transaction_type', 'opening')
                    ->sum('days');

                $accruals = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->whereIn('transaction_type', ['accrual', 'carry_forward'])
                    ->sum('days');

                $used = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('transaction_type', 'leave_approved')
                    ->sum('days');

                $cancelled = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('transaction_type', 'leave_cancelled')
                    ->sum('days');

                $expired = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('transaction_type', 'expiry')
                    ->sum('days');

                $adjustments = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('transaction_type', 'adjustment')
                    ->sum('days');

                $latest = LeaveBalanceLedger::where('user_id', $selectedEmployeeId)
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->orderBy('transaction_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $available = $latest ? (float) $latest->balance_after : 0;

                $entitlement = \App\Models\LeavePolicyDetail::whereHas('policy.assignments', function ($query) use ($selectedEmployeeId) {
                    $query->where('user_id', $selectedEmployeeId)
                        ->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('effective_to')->orWhere('effective_to', '>=', now()->toDateString());
                        });
                })
                    ->where('leave_type_id', $selectedLeaveTypeId)
                    ->where('status', 'active')
                    ->max('annual_entitlement') ?? 0;

                $summary = [
                    'employee' => $employee,
                    'leave_type' => $leaveType,
                    'entitlement' => (float) $entitlement,
                    'opening' => (float) $opening,
                    'accruals' => (float) $accruals,
                    'used' => (float) $used,
                    'cancelled' => (float) $cancelled,
                    'expired' => (float) $expired,
                    'adjustments' => (float) $adjustments,
                    'available' => $available,
                ];
            }
        }

        return Inertia::render('HR/Leave/LeaveLedgers/Summary', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'selectedEmployeeId' => $selectedEmployeeId,
            'selectedLeaveTypeId' => $selectedLeaveTypeId,
            'summary' => $summary,
        ]);
    }

    public function show(LeaveBalanceLedger $ledger): Response
    {
        $ledger->load(['employee', 'leaveType', 'createdBy', 'performedBy', 'approvedBy', 'processedBy']);

        return Inertia::render('HR/Leave/LeaveLedgers/Show', [
            'ledger' => $ledger,
        ]);
    }
}
