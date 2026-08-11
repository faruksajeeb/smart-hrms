<?php

use App\Models\EmployeeEmploymentHistory;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveAccrualService;
use App\Services\HR\Leave\LeaveBalanceService;
use App\Services\HR\Leave\LeaveEligibilityService;
use App\Services\HR\Leave\LeavePolicyResolver;
use Carbon\Carbon;
use Database\Seeders\DummyMasterDataItemSeeder;

test('leave accrual service processes monthly accrual', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->employeeProfile()->create([
        'joining_date' => Carbon::now()->subMonths(3),
        'employment_status' => 'confirmed',
        'gender' => 'male',
        'marital_status' => 'married',
        'confirmation_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
    ]);
    
    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);
    
    $leaveType = LeaveType::factory()->create();
    $policy = LeavePolicy::factory()->create([
        'status' => 'active',
        'effective_from' => Carbon::now()->subYear()->format('Y-m-d'),
        'effective_to' => null,
    ]);
    $detail = LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'monthly',
        'monthly_accrual' => 2,
        'carry_forward_allowed' => true,
        'maximum_carry_forward' => 10,
        'encashment_allowed' => true,
        'maximum_encashment' => 10,
        'maximum_consecutive_days' => 15,
        'minimum_days_per_application' => 1,
        'maximum_days_per_application' => 30,
        'half_day_allowed' => true,
        'hourly_leave_allowed' => false,
        'attachment_required' => false,
        'medical_certificate_required' => false,
        'notice_period_days' => 0,
        'minimum_service_months' => 0,
        'probation_allowed' => true,
        'include_weekly_off' => false,
        'include_holiday' => false,
        'sandwich_rule' => false,
        'allow_negative_balance' => false,
        'gender_restriction' => 'any',
        'marital_status_restriction' => 'any',
        'applicable_after_confirmation' => false,
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);
    
    $assignment = LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $resolver = new LeavePolicyResolver();
    $eligibility = new LeaveEligibilityService($resolver);
    $balanceService = new LeaveBalanceService($resolver);
    $accrualService = new LeaveAccrualService($resolver, $eligibility, $balanceService);
    
    $period = Carbon::now()->startOfMonth();
    $result = $accrualService->accrue($employee, $detail, $period);
    
    expect($result)->not->toBeNull();
    expect($result)->toBeGreaterThan(0);
});