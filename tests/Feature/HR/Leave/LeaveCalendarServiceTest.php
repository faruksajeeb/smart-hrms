<?php

use App\Models\EmployeeEmploymentHistory;
use App\Models\LeaveApplication;
use App\Models\LeaveApplicationDay;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveCalendarService;
use App\Services\HR\Leave\LeaveEligibilityService;
use App\Services\HR\Leave\LeavePolicyResolver;
use Carbon\Carbon;
use Database\Seeders\DummyMasterDataItemSeeder;

test('employee calendar shows own approved leave', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->employeeProfile()->create([
        'joining_date' => Carbon::now()->subMonths(3),
        'employment_status' => 'confirmed',
        'gender' => 'male',
        'marital_status' => 'married',
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
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'total_days' => 3,
        'requested_days' => 3,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $events = $service->getEmployeeCalendar($employee, $from, $to);

    expect($events)->toHaveCount(1);
    expect($events[0]['status'])->toBe('approved');
    expect($events[0]['leave_type']['name'])->toBe($leaveType->leave_name);
});

test('employee calendar does not show other employees leave', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $leaveType = LeaveType::factory()->create();
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    $detail = LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $employeeA->id,
        'updated_by' => $employeeA->id,
    ]);

    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employeeB->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employeeA->id,
        'updated_by' => $employeeA->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000002',
        'user_id' => $employeeB->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
        'total_days' => 3,
        'requested_days' => 3,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $employeeB->id,
        'updated_by' => $employeeB->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $events = $service->getEmployeeCalendar($employeeA, $from, $to);

    expect($events)->toHaveCount(0);
});

test('hr calendar shows approved leave in current month', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $result = $service->getHRCalendar($hrUser, $from, $to);

    expect($result['events'])->toHaveCount(1);
    expect($result['summary']['employees_on_leave'])->toBe(1);
    expect($result['summary']['leave_days'])->toBe(1);
    expect($result['summary']['applications'])->toBe(1);
    expect($result['events'][0]['employee']['name'])->toBe($employee->name);
    expect($result['events'][0]['leave_type']['name'])->toBe($leaveType->leave_name);
});

test('hr calendar respects company scope', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 2, 'branch_id' => 1]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    foreach ([$employeeA, $employeeB] as $emp) {
        LeavePolicyAssignment::create([
            'leave_policy_id' => $policy->id,
            'user_id' => $emp->id,
            'status' => 'active',
            'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
            'effective_to' => null,
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        $app = LeaveApplication::create([
            'application_no' => 'LA' . date('Ym') . rand(100000, 999999),
            'user_id' => $emp->id,
            'leave_policy_id' => $policy->id,
            'leave_type_id' => $leaveType->id,
            'application_type' => 'normal',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'total_days' => 1,
            'requested_days' => 1,
            'is_half_day' => false,
            'is_emergency' => false,
            'status' => 'approved',
            'submitted_at' => Carbon::now(),
            'approved_at' => Carbon::now(),
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        LeaveApplicationDay::create([
            'leave_application_id' => $app->id,
            'leave_date' => Carbon::now()->format('Y-m-d'),
            'day_type' => 'full_day',
            'session' => null,
            'is_holiday' => false,
            'is_weekly_off' => false,
            'counts_as_leave' => true,
            'leave_days' => 1,
        ]);
    }

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $result = $service->getHRCalendar($hrUser, $from, $to);

    expect($result['events'])->toHaveCount(1);
    expect($result['events'][0]['employee']['name'])->toBe($employeeA->name);
});

test('hr calendar filters by status approved only by default', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $approved = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeaveApplicationDay::create([
        'leave_application_id' => $approved->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $rejected = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000002',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'rejected',
        'submitted_at' => Carbon::now(),
        'rejected_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeaveApplicationDay::create([
        'leave_application_id' => $rejected->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();

    $result = $service->getHRCalendar($hrUser, $from, $to);
    expect($result['events'])->toHaveCount(1);

    $resultAll = $service->getHRCalendar($hrUser, $from, $to, ['status' => 'all']);
    expect($resultAll['events'])->toHaveCount(2);
});

test('hr calendar excludes counts_as_leave = 0 by default', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => true,
        'is_weekly_off' => false,
        'counts_as_leave' => false,
        'leave_days' => 0,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $result = $service->getHRCalendar($hrUser, $from, $to);

    expect($result['events'])->toHaveCount(0);

    $resultWithNonLeave = $service->getHRCalendar($hrUser, $from, $to, ['include_non_leave' => true]);
    expect($resultWithNonLeave['events'])->toHaveCount(1);
});

test('hr calendar half-day displays correctly', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 0.5,
        'requested_days' => 0.5,
        'is_half_day' => true,
        'half_day_session' => 'morning',
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'half_day',
        'session' => 'morning',
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 0.5,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $result = $service->getHRCalendar($hrUser, $from, $to);

    expect($result['events'])->toHaveCount(1);
    expect($result['events'][0]['day_type'])->toBe('half_day');
    expect($result['events'][0]['session'])->toBe('morning');
    expect($result['events'][0]['leave_days'])->toBe('0.50');
});

test('hr calendar day details returns correct employees', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employeeA->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    EmployeeEmploymentHistory::create([
        'user_id' => $employeeB->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    foreach ([$employeeA, $employeeB] as $emp) {
        LeavePolicyAssignment::create([
            'leave_policy_id' => $policy->id,
            'user_id' => $emp->id,
            'status' => 'active',
            'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
            'effective_to' => null,
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        $app = LeaveApplication::create([
            'application_no' => 'LA' . date('Ym') . rand(100000, 999999),
            'user_id' => $emp->id,
            'leave_policy_id' => $policy->id,
            'leave_type_id' => $leaveType->id,
            'application_type' => 'normal',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'total_days' => 1,
            'requested_days' => 1,
            'is_half_day' => false,
            'is_emergency' => false,
            'status' => 'approved',
            'submitted_at' => Carbon::now(),
            'approved_at' => Carbon::now(),
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        LeaveApplicationDay::create([
            'leave_application_id' => $app->id,
            'leave_date' => Carbon::now()->format('Y-m-d'),
            'day_type' => 'full_day',
            'session' => null,
            'is_holiday' => false,
            'is_weekly_off' => false,
            'counts_as_leave' => true,
            'leave_days' => 1,
        ]);
    }

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $date = Carbon::now();
    $result = $service->getHRCalendarDayDetails($hrUser, $date);

    expect($result['total'])->toBe(2);
    expect($result['employees'])->toHaveCount(2);
    expect($result['employees'][0]['employee_name'])->toBe($employeeA->name);
    expect($result['employees'][1]['employee_name'])->toBe($employeeB->name);
});

test('hr calendar day details respects status filter', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    $approved = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeaveApplicationDay::create([
        'leave_application_id' => $approved->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $rejected = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000002',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'rejected',
        'submitted_at' => Carbon::now(),
        'rejected_at' => Carbon::now(),
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);
    LeaveApplicationDay::create([
        'leave_application_id' => $rejected->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $date = Carbon::now();

    $approvedResult = $service->getHRCalendarDayDetails($hrUser, $date, ['status' => 'approved']);
    expect($approvedResult['total'])->toBe(1);
    expect($approvedResult['employees'][0]['status'])->toBe('approved');

    $allResult = $service->getHRCalendarDayDetails($hrUser, $date, ['status' => 'all']);
    expect($allResult['total'])->toBe(2);
});

test('hr calendar admin sees all companies', function () {
    $admin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $admin->assignRole('admin');

    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 2, 'branch_id' => 1]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    foreach ([$employeeA, $employeeB] as $emp) {
        LeavePolicyAssignment::create([
            'leave_policy_id' => $policy->id,
            'user_id' => $emp->id,
            'status' => 'active',
            'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
            'effective_to' => null,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $app = LeaveApplication::create([
            'application_no' => 'LA' . date('Ym') . rand(100000, 999999),
            'user_id' => $emp->id,
            'leave_policy_id' => $policy->id,
            'leave_type_id' => $leaveType->id,
            'application_type' => 'normal',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'total_days' => 1,
            'requested_days' => 1,
            'is_half_day' => false,
            'is_emergency' => false,
            'status' => 'approved',
            'submitted_at' => Carbon::now(),
            'approved_at' => Carbon::now(),
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        LeaveApplicationDay::create([
            'leave_application_id' => $app->id,
            'leave_date' => Carbon::now()->format('Y-m-d'),
            'day_type' => 'full_day',
            'session' => null,
            'is_holiday' => false,
            'is_weekly_off' => false,
            'counts_as_leave' => true,
            'leave_days' => 1,
        ]);
    }

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();
    $result = $service->getHRCalendar($admin, $from, $to);

    expect($result['events'])->toHaveCount(2);
});

test('hr calendar filters by department', function () {
    $hrUser = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    $dept1 = MasterDataItem::factory()->create(['category' => MasterDataItem::CATEGORY_DEPARTMENT, 'status' => MasterDataItem::STATUS_ACTIVE]);
    $dept2 = MasterDataItem::factory()->create(['category' => MasterDataItem::CATEGORY_DEPARTMENT, 'status' => MasterDataItem::STATUS_ACTIVE]);
    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1, 'department_id' => $dept1->id]);
    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1, 'department_id' => $dept2->id]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $hrUser->id,
        'updated_by' => $hrUser->id,
    ]);

    foreach ([$employeeA, $employeeB] as $emp) {
        LeavePolicyAssignment::create([
            'leave_policy_id' => $policy->id,
            'user_id' => $emp->id,
            'status' => 'active',
            'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
            'effective_to' => null,
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        $app = LeaveApplication::create([
            'application_no' => 'LA' . date('Ym') . rand(100000, 999999),
            'user_id' => $emp->id,
            'leave_policy_id' => $policy->id,
            'leave_type_id' => $leaveType->id,
            'application_type' => 'normal',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
            'total_days' => 1,
            'requested_days' => 1,
            'is_half_day' => false,
            'is_emergency' => false,
            'status' => 'approved',
            'submitted_at' => Carbon::now(),
            'approved_at' => Carbon::now(),
            'created_by' => $hrUser->id,
            'updated_by' => $hrUser->id,
        ]);

        LeaveApplicationDay::create([
            'leave_application_id' => $app->id,
            'leave_date' => Carbon::now()->format('Y-m-d'),
            'day_type' => 'full_day',
            'session' => null,
            'is_holiday' => false,
            'is_weekly_off' => false,
            'counts_as_leave' => true,
            'leave_days' => 1,
        ]);
    }

    $service = new LeaveCalendarService(new LeavePolicyResolver(), new LeaveEligibilityService(new LeavePolicyResolver()));
    $from = Carbon::now()->startOfMonth();
    $to = Carbon::now()->endOfMonth();

    $resultAll = $service->getHRCalendar($hrUser, $from, $to);
    expect($resultAll['events'])->toHaveCount(2);

    $resultDept1 = $service->getHRCalendar($hrUser, $from, $to, ['department_id' => $dept1->id]);
    expect($resultDept1['events'])->toHaveCount(1);
    expect($resultDept1['events'][0]['employee']['department_id'])->toBe($dept1->id);
});

test('hr calendar controller returns correct data', function () {
    $admin = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE, 'company_id' => 1, 'branch_id' => 1]);
    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'initial_appointment',
        'company_id' => 1,
        'branch_id' => 1,
        'department_id' => 1,
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => Carbon::now()->subYear()->format('Y-m-d'), 'effective_to' => null]);
    LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 24,
        'accrual_method' => 'none',
        'monthly_accrual' => 0,
        'status' => 'active',
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => Carbon::now()->subMonths(3)->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $application = LeaveApplication::create([
        'application_no' => 'LA' . date('Ym') . '000001',
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'application_type' => 'normal',
        'start_date' => Carbon::now()->format('Y-m-d'),
        'end_date' => Carbon::now()->format('Y-m-d'),
        'total_days' => 1,
        'requested_days' => 1,
        'is_half_day' => false,
        'is_emergency' => false,
        'status' => 'approved',
        'submitted_at' => Carbon::now(),
        'approved_at' => Carbon::now(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    LeaveApplicationDay::create([
        'leave_application_id' => $application->id,
        'leave_date' => Carbon::now()->format('Y-m-d'),
        'day_type' => 'full_day',
        'session' => null,
        'is_holiday' => false,
        'is_weekly_off' => false,
        'counts_as_leave' => true,
        'leave_days' => 1,
    ]);

    $response = $this->get(route('hr.leave.calendar.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/Calendar/Index'));
});