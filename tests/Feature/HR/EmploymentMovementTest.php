<?php

use App\Enums\EmploymentMovementType;
use App\Models\EmployeeEmploymentHistory;
use App\Models\MasterDataItem;
use App\Models\User;
use Database\Seeders\DummyMasterDataItemSeeder;

beforeEach(function () {
    $this->seed(DummyMasterDataItemSeeder::class);
});

test('hr users can view the employment movements index page', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.employment-movements.index'))
        ->assertOk();
});

test('hr users can create an initial appointment', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();
    $designation = MasterDataItem::where('category', 'designation')->first();
    $employmentType = MasterDataItem::where('category', 'employee_type')->first();

    $employee = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'designation_id' => $designation->id,
        'employment_type_id' => $employmentType->id,
        'joining_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($hr)->post(route('hr.employment-movements.store'), [
        'employee_id' => $employee->id,
        'event_type' => EmploymentMovementType::InitialAppointment->value,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'designation_id' => $designation->id,
        'employment_type_id' => $employmentType->id,
        'effective_from' => now()->toDateString(),
        'reason' => 'Initial appointment',
        'remarks' => 'Onboarding',
    ]);

    $response->assertSessionHasNoErrors();

    $history = EmployeeEmploymentHistory::where('user_id', $employee->id)->first();

    expect($history)->not->toBeNull();
    expect($history->event_type)->toBe(EmploymentMovementType::InitialAppointment->value);
});

test('hr users can create a transfer movement', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();

    $employee = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
    ]);

    $this->actingAs($hr)->post(route('hr.employment-movements.store'), [
        'employee_id' => $employee->id,
        'event_type' => EmploymentMovementType::Transfer->value,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'effective_from' => now()->addDay()->toDateString(),
        'reason' => 'Transfer',
    ])->assertRedirect();

    $history = EmployeeEmploymentHistory::where('user_id', $employee->id)
        ->where('event_type', EmploymentMovementType::Transfer->value)
        ->first();

    expect($history)->not->toBeNull();
    expect($history->effective_to)->toBeNull();
});

test('employment movement validates timeline and prevents overlaps', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();

    $employee = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
    ]);

    EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => EmploymentMovementType::InitialAppointment->value,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'effective_from' => now()->toDateString(),
        'effective_to' => null,
        'reason' => 'Initial',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.employment-movements.store'), [
        'employee_id' => $employee->id,
        'event_type' => EmploymentMovementType::Transfer->value,
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'effective_from' => now()->toDateString(),
        'reason' => 'Overlap',
    ])->assertSessionHasErrors('effective_from');
});

test('employment movement rejects past effective date', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->post(route('hr.employment-movements.store'), [
        'employee_id' => $employee->id,
        'event_type' => EmploymentMovementType::Transfer->value,
        'company_id' => $company->id,
        'effective_from' => now()->subDay()->toDateString(),
        'reason' => 'Past date',
    ])->assertSessionHasErrors('effective_from');
});

test('hr users can view employment movement history for employee', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->get(route('hr.employment-movements.history', $employee->id))
        ->assertOk();
});

test('hr users can delete an active employment movement', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();

    $employee = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'company_id' => $company->id,
    ]);

    $movement = EmployeeEmploymentHistory::create([
        'user_id' => $employee->id,
        'event_type' => EmploymentMovementType::Transfer->value,
        'company_id' => $company->id,
        'effective_from' => now()->addDay()->toDateString(),
        'effective_to' => null,
        'reason' => 'Transfer',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->delete(route('hr.employment-movements.destroy', $movement->id))
        ->assertRedirect();

    expect(EmployeeEmploymentHistory::withTrashed()->find($movement->id))->not->toBeNull();
    expect(EmployeeEmploymentHistory::find($movement->id))->toBeNull();
});
