<?php

use App\Models\EmployeeProfile;
use App\Models\EmployeeReportingManagerAssignment;
use App\Models\MasterDataItem;
use App\Models\User;
use Database\Seeders\DummyMasterDataItemSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('hr users can view the reporting manager assignment page', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.reporting-manager-assignments.index'))
        ->assertOk();
});

test('hr users can assign a reporting manager to an employee', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);

    $manager = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $manager->assignRole(User::ROLE_EMPLOYEE);

    $this->actingAs($hr)->post(route('hr.reporting-manager-assignments.store'), [
        'employee_id' => $employee->id,
        'manager_id' => $manager->id,
        'effective_from' => now()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'Onboarding manager.',
    ])->assertRedirect();

    $assignment = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
        ->where('manager_id', $manager->id)
        ->where('is_current', true)
        ->first();

    expect($assignment)->not->toBeNull();
    expect(\Carbon\Carbon::parse($assignment->effective_from)->toDateString())->toBe(now()->toDateString());
});

test('hr users can change a reporting manager and close the previous assignment', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);

    $oldManager = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $oldManager->assignRole(User::ROLE_EMPLOYEE);

    $newManager = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $newManager->assignRole(User::ROLE_EMPLOYEE);

    EmployeeReportingManagerAssignment::create([
        'user_id' => $employee->id,
        'manager_id' => $oldManager->id,
        'effective_from' => now()->subDays(10),
        'effective_to' => null,
        'assignment_type' => 'initial',
        'is_current' => true,
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $currentAssignment = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
        ->where('is_current', true)
        ->first();

    $this->actingAs($hr)->post(route('hr.reporting-manager-assignments.store-change', $currentAssignment->id), [
        'employee_id' => $employee->id,
        'manager_id' => $newManager->id,
        'effective_from' => now()->addDay()->toDateString(),
        'assignment_type' => 'transfer',
        'remarks' => 'Manager transferred.',
    ])->assertRedirect();

    $oldAssignment = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
        ->where('manager_id', $oldManager->id)
        ->first();

    expect($oldAssignment->effective_to)->not->toBeNull();
    expect((bool) $oldAssignment->is_current)->toBeFalse();

    $newAssignment = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
        ->where('manager_id', $newManager->id)
        ->where('is_current', true)
        ->first();

    expect($newAssignment)->not->toBeNull();
    expect(\Carbon\Carbon::parse($newAssignment->effective_from)->toDateString())->toBe(now()->addDay()->toDateString());
});

test('prevent employee from being their own reporting manager', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);

    $this->actingAs($hr)->post(route('hr.reporting-manager-assignments.store'), [
        'employee_id' => $employee->id,
        'manager_id' => $employee->id,
        'effective_from' => now()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'Self manager.',
    ])->assertSessionHasErrors('employee_id');
});

test('prevent circular reporting relationship', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employeeA = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employeeA->assignRole(User::ROLE_EMPLOYEE);

    $employeeB = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employeeB->assignRole(User::ROLE_EMPLOYEE);

    $employeeC = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employeeC->assignRole(User::ROLE_EMPLOYEE);

    EmployeeReportingManagerAssignment::create([
        'user_id' => $employeeA->id,
        'manager_id' => $employeeB->id,
        'effective_from' => now()->subDays(5),
        'effective_to' => null,
        'assignment_type' => 'initial',
        'is_current' => true,
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.reporting-manager-assignments.store'), [
        'employee_id' => $employeeB->id,
        'manager_id' => $employeeC->id,
        'effective_from' => now()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'B manager C.',
    ])->assertRedirect();

    $this->actingAs($hr)->post(route('hr.reporting-manager-assignments.store'), [
        'employee_id' => $employeeC->id,
        'manager_id' => $employeeA->id,
        'effective_from' => now()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'Circular.',
    ])->assertSessionHasErrors('manager_id');
});
