<?php

use App\Models\LeaveApplication;
use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->withoutVite();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $permissions = [
        'leave.view-own-applications',
        'leave.apply',
        'leave.edit-own-application',
        'leave.submit-application',
        'leave.cancel-own-application',
        'leave.withdraw-application',
        'leave.view-all-applications',
        'leave.manage-applications',
        'leave.approve-application',
        'leave.reject-application',
        'leave.upload-attachment',
        'leave.delete-attachment',
    ];

    foreach ($permissions as $permission) {
        Permission::updateOrCreate(
            ['name' => $permission, 'guard_name' => 'web'],
            ['group_name' => 'leave']
        );
    }
});

it('allows employee to view leave applications index', function () {
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);
    $employee->givePermissionTo(['leave.view-own-applications']);

    $this->actingAs($employee);

    $response = $this->get(route('employee.leave.applications.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Employee/Leave/Applications/Index'));
});

it('allows employee to create leave application draft', function () {
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);
    $employee->givePermissionTo(['leave.apply']);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create(['status' => 'active', 'effective_from' => now()->subYear()->format('Y-m-d')]);

    \App\Models\LeavePolicyAssignment::create([
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'effective_from' => now()->subYear()->format('Y-m-d'),
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    \App\Models\LeavePolicyDetail::create([
        'leave_policy_id' => $policy->id,
        'leave_type_id' => $leaveType->id,
        'annual_entitlement' => 10,
        'accrual_method' => 'none',
        'minimum_days_per_application' => 1,
        'notice_period_days' => 0,
        'gender_restriction' => 'any',
        'marital_status_restriction' => 'any',
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $this->actingAs($employee);

    $response = $this->post(route('employee.leave.applications.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->addDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(7)->format('Y-m-d'),
        'is_half_day' => false,
        'is_emergency' => false,
        'reason' => 'Vacation',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('leave_applications', [
        'user_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'status' => 'draft',
    ]);
});

it('allows employee to edit draft leave application', function () {
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);
    $employee->givePermissionTo(['leave.edit-own-application']);

    $application = LeaveApplication::factory()->create([
        'user_id' => $employee->id,
        'status' => 'draft',
    ]);

    $this->actingAs($employee);

    $response = $this->get(route('employee.leave.applications.edit', $application));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Employee/Leave/Applications/Edit'));
});

it('allows employee to submit leave application', function () {
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);
    $employee->givePermissionTo(['leave.submit-application']);

    $leaveType = LeaveType::factory()->create(['status' => 'active']);
    $policy = LeavePolicy::factory()->create([
        'status' => 'active',
        'effective_from' => now()->subYear()->format('Y-m-d'),
        'effective_to' => null,
    ]);

    \App\Models\EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => 'joining',
        'company_id' => $employee->company_id,
        'branch_id' => $employee->branch_id,
        'effective_from' => $employee->joining_date ?? now()->subYear()->format('Y-m-d'),
        'status' => \App\Enums\EmploymentHistoryStatus::Approved,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    \App\Models\LeavePolicyAssignment::create([
        'user_id' => $employee->id,
        'leave_policy_id' => $policy->id,
        'effective_from' => now()->subYear()->format('Y-m-d'),
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    \App\Models\LeaveOpeningBalance::create([
        'user_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'opening_balance' => 10,
        'effective_date' => now()->subYear()->format('Y-m-d'),
        'status' => 'active',
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    \App\Models\LeaveBalanceLedger::create([
        'user_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'transaction_type' => \App\Enums\LeaveTransactionType::Opening,
        'transaction_date' => now()->subYear()->format('Y-m-d'),
        'effective_date' => now()->subYear()->format('Y-m-d'),
        'days' => 10,
        'credit_days' => 10,
        'debit_days' => 0,
        'balance_after' => 10,
        'transaction_source' => 'system',
        'company_id' => $employee->company_id,
        'branch_id' => $employee->branch_id,
        'created_by' => $employee->id,
    ]);

    $application = LeaveApplication::factory()->create([
        'user_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'leave_policy_id' => $policy->id,
        'status' => 'draft',
        'start_date' => now()->addDays(5)->format('Y-m-d'),
        'end_date' => now()->addDays(7)->format('Y-m-d'),
    ]);

    $this->actingAs($employee);

    $response = $this->post(route('employee.leave.applications.submit', $application));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $this->assertDatabaseHas('leave_applications', [
        'id' => $application->id,
    ]);
    $this->assertTrue(
        in_array($application->fresh()->status->value, ['pending', 'approved']),
        'Expected status to be pending or approved, got: ' . $application->fresh()->status->value
    );
});

it('prevents employee from accessing other employee leave applications', function () {
    $employee1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee1->assignRole(User::ROLE_EMPLOYEE);
    $employee1->givePermissionTo(['leave.view-own-applications']);

    $employee2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee2->assignRole(User::ROLE_EMPLOYEE);

    $application = LeaveApplication::factory()->create([
        'user_id' => $employee2->id,
        'status' => 'draft',
    ]);

    $this->actingAs($employee1);

    $response = $this->get(route('employee.leave.applications.show', $application));

    $response->assertStatus(403);
});
