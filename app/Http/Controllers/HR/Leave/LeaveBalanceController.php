<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveBalanceService;
use App\Services\HR\Leave\LeaveEligibilityService;
use App\Services\HR\Leave\LeavePolicyResolver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveBalanceController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $balanceService,
        protected LeavePolicyResolver $policyResolver,
        protected LeaveEligibilityService $eligibilityService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $employees = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        return Inertia::render('HR/Leave/LeaveBalances/Index', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'filters' => [
                'employee_id' => $request->integer('employee_id'),
                'leave_type_id' => $request->integer('leave_type_id'),
            ],
        ]);
    }

    public function show(Request $request, User $employee, LeaveType $leaveType): Response
    {
        $asOfDate = $request->string('as_of_date', now()->format('Y-m-d'))->toString();
        
        $balance = $this->balanceService->getBalance($employee, $leaveType, $asOfDate);
        $breakdown = $this->balanceService->getBalanceBreakdown($employee, $leaveType, $asOfDate);
        $ledger = $this->balanceService->getLedger($employee, $leaveType);
        
        $policy = $this->policyResolver->getPolicy($employee, \Carbon\Carbon::parse($asOfDate));
        $eligibility = $this->eligibilityService->canApply($employee, $leaveType->id, \Carbon\Carbon::parse($asOfDate));

        return Inertia::render('HR/Leave/LeaveBalances/Show', [
            'employee' => $employee,
            'leaveType' => $leaveType,
            'balance' => $balance,
            'breakdown' => $breakdown,
            'ledger' => $ledger,
            'policy' => $policy,
            'eligibility' => $eligibility,
            'asOfDate' => $asOfDate,
        ]);
    }

    public function employeeBalances(Request $request, User $employee): Response
    {
        $asOfDate = $request->string('as_of_date', now()->format('Y-m-d'))->toString();
        
        $balances = $this->balanceService->getAllBalances($employee);
        
        $detailedBalances = [];
        foreach ($balances as $balance) {
            $leaveType = LeaveType::find($balance['leave_type_id']);
            if (!$leaveType) {
                continue;
            }
            
            $breakdown = $this->balanceService->getBalanceBreakdown($employee, $leaveType, $asOfDate);
            $detailedBalances[] = array_merge($balance, ['breakdown' => $breakdown]);
        }

        return Inertia::render('HR/Leave/LeaveBalances/EmployeeBalances', [
            'employee' => $employee,
            'balances' => $detailedBalances,
            'asOfDate' => $asOfDate,
        ]);
    }

    public function ledger(Request $request, User $employee, LeaveType $leaveType): Response
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->string('from')) : null;
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->string('to')) : null;
        
        $ledger = $this->balanceService->getLedger($employee, $leaveType, $from, $to);
        
        return Inertia::render('HR/Leave/LeaveBalances/Ledger', [
            'employee' => $employee,
            'leaveType' => $leaveType,
            'ledger' => $ledger,
            'filters' => [
                'from' => $request->string('from'),
                'to' => $request->string('to'),
            ],
        ]);
    }
}