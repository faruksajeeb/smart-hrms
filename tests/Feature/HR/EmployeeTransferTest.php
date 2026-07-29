<?php

use App\Models\EmployeeTransfer;
use App\Models\MasterDataItem;
use App\Models\User;
use Database\Seeders\DummyMasterDataItemSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    $this->seed(DummyMasterDataItemSeeder::class);
});

test('hr users can view the employee transfer index page', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.transfers.index'))
        ->assertOk();
});

test('hr users can create an employee transfer', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->post(route('hr.transfers.store'), [
        'employee_id' => $employee->id,
        'to_company_id' => $company->id,
        'to_branch_id' => $branch->id,
        'to_department_id' => $department->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
        'remarks' => 'Promoted to new department.',
    ])->assertRedirect();

    $transfer = EmployeeTransfer::where('user_id', $employee->id)->first();

    expect($transfer)->not->toBeNull();
    expect($transfer->to_company_id)->toBe($company->id);
    expect($transfer->to_department_id)->toBe($department->id);
    expect($transfer->approval_status)->toBe('draft');
});

test('transfer creation fails when destination is same as current', function () {
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

    $this->actingAs($hr)->post(route('hr.transfers.store'), [
        'employee_id' => $employee->id,
        'to_company_id' => $company->id,
        'to_branch_id' => $branch->id,
        'to_department_id' => $department->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
    ])->assertSessionHasErrors();
});

test('transfer creation fails with past effective date', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->post(route('hr.transfers.store'), [
        'employee_id' => $employee->id,
        'to_company_id' => $company->id,
        'effective_from' => now()->subDay()->toDateString(),
        'transfer_reason' => 'promotion',
    ])->assertSessionHasErrors('effective_from');
});

test('transfer creation fails when employee already has approved transfer', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    EmployeeTransfer::create([
        'user_id' => $employee->id,
        'from_company_id' => $employee->company_id,
        'from_branch_id' => $employee->branch_id,
        'from_department_id' => $employee->department_id,
        'to_company_id' => $company->id,
        'to_branch_id' => $branch->id,
        'to_department_id' => $department->id,
        'effective_from' => now()->addDays(5)->toDateString(),
        'transfer_reason' => 'promotion',
        'approval_status' => 'approved',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.transfers.store'), [
        'employee_id' => $employee->id,
        'to_company_id' => $company->id,
        'to_branch_id' => $branch->id,
        'to_department_id' => $department->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
    ])->assertSessionHasErrors('employee_id');
});

test('hr users can approve a transfer and update employee master data', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();
    $branch = MasterDataItem::where('category', 'branch')->first();
    $department = MasterDataItem::where('category', 'department')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $transfer = EmployeeTransfer::create([
        'user_id' => $employee->id,
        'from_company_id' => $employee->company_id,
        'from_branch_id' => $employee->branch_id,
        'from_department_id' => $employee->department_id,
        'to_company_id' => $company->id,
        'to_branch_id' => $branch->id,
        'to_department_id' => $department->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
        'approval_status' => 'draft',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.transfers.approve', $transfer->id))
        ->assertRedirect();

    $transfer->refresh();
    $employee->refresh();

    expect($transfer->approval_status)->toBe('approved');
    expect($employee->company_id)->toBe($company->id);
    expect($employee->branch_id)->toBe($branch->id);
    expect($employee->department_id)->toBe($department->id);
});

test('hr users can reject a transfer', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $transfer = EmployeeTransfer::create([
        'user_id' => $employee->id,
        'from_company_id' => $employee->company_id,
        'to_company_id' => $company->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
        'approval_status' => 'draft',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->post(route('hr.transfers.reject', $transfer->id))
        ->assertRedirect();

    $transfer->refresh();

    expect($transfer->approval_status)->toBe('rejected');
});

test('hr users can edit a draft transfer', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $newCompany = MasterDataItem::create([
        'category' => 'company',
        'code' => 'NEWCO',
        'name' => 'New Company',
        'status' => MasterDataItem::STATUS_ACTIVE,
    ]);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $transfer = EmployeeTransfer::create([
        'user_id' => $employee->id,
        'from_company_id' => $employee->company_id,
        'to_company_id' => $employee->company_id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
        'approval_status' => 'draft',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->put(route('hr.transfers.update', $transfer->id), [
        'to_company_id' => $newCompany->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
    ])->assertRedirect();

    $transfer->refresh();

    expect($transfer->to_company_id)->toBe($newCompany->id);
});

test('hr users can delete a draft transfer', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::where('category', 'company')->first();

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $transfer = EmployeeTransfer::create([
        'user_id' => $employee->id,
        'from_company_id' => $employee->company_id,
        'to_company_id' => $company->id,
        'effective_from' => now()->addDay()->toDateString(),
        'transfer_reason' => 'promotion',
        'approval_status' => 'draft',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $this->actingAs($hr)->delete(route('hr.transfers.destroy', $transfer->id))
        ->assertRedirect();

    expect(EmployeeTransfer::withTrashed()->find($transfer->id))->not->toBeNull();
    expect(EmployeeTransfer::find($transfer->id))->toBeNull();
});

test('create page pre-selects employee when passed via query parameter', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->get(route('hr.transfers.create', ['employee_id' => $employee->id]))
        ->assertOk()
        ->assertSee($employee->name);
});

test('transfer history page loads for employee', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $this->actingAs($hr)->get(route('hr.transfers.history', $employee->id))
        ->assertOk();
});
