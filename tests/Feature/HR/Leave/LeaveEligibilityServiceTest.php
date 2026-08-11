<?php

use App\Models\EmployeeEmploymentHistory;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveEligibilityService;
use App\Services\HR\Leave\LeavePolicyResolver;
use Carbon\Carbon;
use Database\Seeders\DummyMasterDataItemSeeder;

test('leave eligibility service checks employee active status', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $employee = User::factory()->create(['status' => User::STATUS_INACTIVE]);
    $resolver = new LeavePolicyResolver();
    $eligibility = new LeaveEligibilityService($resolver);
    
    $result = $eligibility->canApply($employee, 1, Carbon::now());
    
    expect($result['eligible'])->toBeFalse();
    expect($result['reasons'])->toContain('Employee is not active.');
});

test('leave eligibility service checks minimum service', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $joiningDate = Carbon::now()->subMonth();
    $employee->employeeProfile()->create([
        'joining_date' => $joiningDate,
        'employment_status' => 'probation',
    ]);
    
    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => $joiningDate->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);
    
    $policy = LeavePolicy::factory()->create([
        'status' => 'active',
        'effective_from' => Carbon::now()->subYear()->format('Y-m-d'),
        'effective_to' => null,
    ]);
    $leaveType = LeaveType::factory()->create();
    
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'carry_forward_allowed' => false,
        'maximum_carry_forward' => null,
        'encashment_allowed' => false,
        'maximum_encashment' => null,
        'maximum_consecutive_days' => null,
        'minimum_days_per_application' => 1,
        'maximum_days_per_application' => null,
        'half_day_allowed' => true,
        'hourly_leave_allowed' => false,
        'attachment_required' => false,
        'medical_certificate_required' => false,
        'notice_period_days' => 0,
        'minimum_service_months' => 3,
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
    
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonth()->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);
    
    $resolver = new LeavePolicyResolver();
    $eligibility = new LeaveEligibilityService($resolver);
    
    $result = $eligibility->canApply($employee, $leaveType->id, Carbon::now());
    
    expect($result['eligible'])->toBeFalse();
    expect($result['reasons'])->toContain('Employee has not completed the minimum service period required for this leave type.');
});