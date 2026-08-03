<?php

use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\HolidayCalendar;
use App\Models\LeaveOpeningBalance;
use App\Models\LeaveBalanceLedger;
use App\Models\MasterDataItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->withoutVite();
});

it('allows hr to view leave types index', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.types.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeaveTypes/Index'));
});

it('allows hr to create leave type', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->post(route('hr.leave.types.store'), [
        'leave_name' => 'Test Leave',
        'leave_code' => 'TL-' . rand(100, 999),
        'description' => 'Test leave type',
        'is_paid' => true,
        'display_color' => '#FF0000',
        'display_order' => 1,
        'status' => 'active',
    ]);

    $response->assertRedirect(route('hr.leave.types.index'));
    expect(LeaveType::where('leave_name', 'Test Leave')->exists())->toBeTrue();
});

it('allows hr to update leave type', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $leaveType = LeaveType::factory()->create();

    $response = $this->actingAs($hr)->put(route('hr.leave.types.update', $leaveType), [
        'leave_name' => 'Updated Leave',
        'leave_code' => $leaveType->leave_code,
        'description' => 'Updated description',
        'is_paid' => false,
        'display_color' => '#00FF00',
        'display_order' => 2,
        'status' => 'inactive',
    ]);

    $response->assertRedirect(route('hr.leave.types.index'));
    expect(LeaveType::find($leaveType->id)->leave_name)->toBe('Updated Leave');
});

it('allows hr to delete leave type', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $leaveType = LeaveType::factory()->create();

    $response = $this->actingAs($hr)->delete(route('hr.leave.types.destroy', $leaveType));

    $response->assertSessionHas('success');
    expect(LeaveType::withTrashed()->find($leaveType->id)->deleted_at)->not->toBeNull();
});

it('allows hr to view leave policies index', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.policies.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeavePolicies/Index'));
});

it('allows hr to create leave policy', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->post(route('hr.leave.policies.store'), [
        'policy_name' => 'Test Policy',
        'policy_code' => 'TP-' . rand(100, 999),
        'description' => 'Test policy',
        'effective_from' => '2024-01-01',
        'effective_to' => '2024-12-31',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('hr.leave.policies.index'));
    expect(LeavePolicy::where('policy_name', 'Test Policy')->exists())->toBeTrue();
});

it('allows hr to view policy details', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $policy = LeavePolicy::factory()->create();

    $response = $this->actingAs($hr)->get(route('hr.leave.policies.details.index', $policy));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeavePolicyDetails/Index'));
});

it('allows hr to view policy assignments', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.assignments.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeavePolicyAssignments/Index'));
});

it('allows hr to view policy assignments with filters', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company = MasterDataItem::create([
        'category' => MasterDataItem::CATEGORY_COMPANY,
        'code' => 'TEST-CO',
        'name' => 'Test Company',
        'status' => MasterDataItem::STATUS_ACTIVE,
    ]);

    $branch = MasterDataItem::create([
        'category' => MasterDataItem::CATEGORY_BRANCH,
        'code' => 'TEST-BR',
        'name' => 'Test Branch',
        'parent_id' => $company->id,
        'status' => MasterDataItem::STATUS_ACTIVE,
    ]);

    $division = MasterDataItem::create([
        'category' => MasterDataItem::CATEGORY_DIVISION,
        'code' => 'TEST-DIV',
        'name' => 'Test Division',
        'parent_id' => $branch->id,
        'status' => MasterDataItem::STATUS_ACTIVE,
    ]);

    $policy = LeavePolicy::factory()->create(['status' => 'active']);

    $employee = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
        'employee_id' => 'EMP-001',
    ]);

    $response = $this->actingAs($hr)->get(route('hr.leave.assignments.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeavePolicyAssignments/Index')
        ->where('assignments.data', [])
        ->where('filters', [])
        ->where('companies', fn ($companies) => count($companies) >= 1)
        ->where('branches', fn ($branches) => count($branches) >= 1)
        ->where('divisions', fn ($divisions) => count($divisions) >= 1)
        ->where('policies', fn ($policies) => count($policies) >= 1)
        ->where('users', fn ($users) => count($users) >= 1)
    );
});

it('allows hr to view holidays index', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.holidays.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/Holidays/Index'));
});

it('allows hr to create holiday', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->post(route('hr.leave.holidays.store'), [
        'holiday_name' => 'Test Holiday',
        'holiday_code' => 'TH-' . rand(100, 999),
        'holiday_date' => '2024-12-25',
        'holiday_type' => 'national',
        'is_recurring' => true,
        'description' => 'Test holiday',
        'status' => 'active',
    ]);

    $response->assertRedirect(route('hr.leave.holidays.index'));
    expect(HolidayCalendar::where('holiday_name', 'Test Holiday')->exists())->toBeTrue();
});

it('allows hr to view opening balances index', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.opening-balances.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/OpeningBalances/Index'));
});

it('allows hr to view leave ledger', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $response = $this->actingAs($hr)->get(route('hr.leave.ledgers.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('HR/Leave/LeaveLedgers/Index'));
});

it('calculates leave balance from ledger', function () {
    $user = User::factory()->create();
    $leaveType = LeaveType::factory()->create();

    $balanceService = app(\App\Services\HR\Leave\LeaveBalanceService::class);
    $balanceService->createLedgerEntry([
        'user_id' => $user->id,
        'leave_type_id' => $leaveType->id,
        'transaction_type' => 'opening',
        'transaction_date' => '2024-01-01',
        'days' => 15,
        'balance_after' => 15,
        'remarks' => 'Opening balance',
    ]);

    $balanceService->createLedgerEntry([
        'user_id' => $user->id,
        'leave_type_id' => $leaveType->id,
        'transaction_type' => 'accrual',
        'transaction_date' => '2024-02-01',
        'days' => 1.75,
        'balance_after' => 16.75,
        'remarks' => 'Monthly accrual',
    ]);

    expect($balanceService->getBalance($user, $leaveType))->toBe(16.75);
});

it('prevents employee from accessing leave master pages', function () {
    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee->assignRole(User::ROLE_EMPLOYEE);

    $this->actingAs($employee)->get(route('hr.leave.types.index'))->assertStatus(403);
    $this->actingAs($employee)->get(route('hr.leave.policies.index'))->assertStatus(403);
    $this->actingAs($employee)->get(route('hr.leave.holidays.index'))->assertStatus(403);
});
