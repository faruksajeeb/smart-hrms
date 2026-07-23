<?php

use App\Models\EmployeeProfile;
use App\Models\EmployeeShiftAssignment;
use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\MasterDataItem;
use App\Models\Shift;
use App\Models\User;
use App\Models\WeeklyOffPolicy;
use App\Models\WeeklyOffPolicyDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('hr users can view the bulk assignment page', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.bulk-assignments.index'))
        ->assertOk();
});

test('hr users can bulk assign shift and weekly off to employees', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    Schema::create('companies', function ($table) {
        $table->id();
        $table->timestamps();
    });

    DB::table('companies')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    User::factory()->create(['id' => 1, 'status' => User::STATUS_ACTIVE]);

    $company = MasterDataItem::where('category', MasterDataItem::CATEGORY_COMPANY)->firstOrFail();
    $branch = MasterDataItem::where('category', MasterDataItem::CATEGORY_BRANCH)->firstOrFail();
    $department = MasterDataItem::where('code', 'REC')->firstOrFail();
    $designation = MasterDataItem::where('code', 'HR-OFF')->firstOrFail();

    $shift = Shift::create([
        'company_id' => 1,
        'shift_name' => 'General',
        'shift_code' => 'GEN',
        'description' => 'General office shift',
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'break_start' => '13:00:00',
        'break_end' => '14:00:00',
        'grace_time' => 15,
        'working_hours' => 8.00,
        'late_after' => 15,
        'half_day_after' => 240,
        'minimum_work_hours' => 8.00,
        'is_flexible' => false,
        'is_night_shift' => false,
        'color' => '#3B82F6',
        'status' => true,
        'created_by' => 1,
        'updated_by' => 1,
    ]);

    $weeklyOffPolicy = WeeklyOffPolicy::create([
        'company_id' => $company->id,
        'policy_name' => 'Corporate Weekend',
        'policy_code' => 'CORP-001',
        'description' => 'Friday and Saturday weekly off.',
        'status' => true,
    ]);

    WeeklyOffPolicyDay::create([
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'day_of_week' => 5,
        'week_type' => 'every',
        'off_type' => 'full_day',
    ]);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employee1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee1->assignRole(User::ROLE_EMPLOYEE);
    $employee1->masterDataItems()->attach([
        $company->id,
        $branch->id,
        $department->id,
        $designation->id,
    ]);
    $employee1->employeeProfile()->create([
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department' => $department->name,
        'designation' => $designation->name,
        'employment_type' => 'probation',
    ]);

    $employee2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee2->assignRole(User::ROLE_EMPLOYEE);
    $employee2->masterDataItems()->attach([
        $company->id,
        $branch->id,
        $department->id,
        $designation->id,
    ]);
    $employee2->employeeProfile()->create([
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department' => $department->name,
        'designation' => $designation->name,
        'employment_type' => 'probation',
    ]);

    $employee3 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee3->assignRole(User::ROLE_EMPLOYEE);
    $employee3->masterDataItems()->attach([
        $company->id,
        $branch->id,
        $department->id,
        $designation->id,
    ]);
    $employee3->employeeProfile()->create([
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department' => $department->name,
        'designation' => $designation->name,
        'employment_type' => 'probation',
    ]);

    $this->actingAs($hr)->post(route('hr.bulk-assignments.store'), [
        'employee_ids' => [$employee1->id, $employee2->id, $employee3->id],
        'shift_id' => $shift->id,
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'effective_from' => now()->addDay()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'Bulk onboarding.',
    ]);

    expect(EmployeeShiftAssignment::where('user_id', $employee1->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeWeeklyOffAssignment::where('user_id', $employee1->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeShiftAssignment::where('user_id', $employee2->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeWeeklyOffAssignment::where('user_id', $employee2->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeShiftAssignment::where('user_id', $employee3->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeWeeklyOffAssignment::where('user_id', $employee3->id)->where('is_current', true)->count())->toBe(1);
});

test('hr users can bulk assign shifts and close existing assignments with different values', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    Schema::create('companies', function ($table) {
        $table->id();
        $table->timestamps();
    });

    DB::table('companies')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);
    User::factory()->create(['id' => 1, 'status' => User::STATUS_ACTIVE]);

    $company = MasterDataItem::where('category', MasterDataItem::CATEGORY_COMPANY)->firstOrFail();
    $branch = MasterDataItem::where('category', MasterDataItem::CATEGORY_BRANCH)->firstOrFail();
    $department = MasterDataItem::where('code', 'REC')->firstOrFail();
    $designation = MasterDataItem::where('code', 'HR-OFF')->firstOrFail();

    $morningShift = Shift::create([
        'company_id' => 1,
        'shift_name' => 'Morning',
        'shift_code' => 'MOR',
        'description' => 'General office shift',
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'break_start' => '13:00:00',
        'break_end' => '14:00:00',
        'grace_time' => 15,
        'working_hours' => 8.00,
        'late_after' => 15,
        'half_day_after' => 240,
        'minimum_work_hours' => 8.00,
        'is_flexible' => false,
        'is_night_shift' => false,
        'color' => '#3B82F6',
        'status' => true,
        'created_by' => 1,
        'updated_by' => 1,
    ]);

    $generalShift = Shift::create([
        'company_id' => 1,
        'shift_name' => 'General',
        'shift_code' => 'GEN',
        'description' => 'General office shift',
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'break_start' => '13:00:00',
        'break_end' => '14:00:00',
        'grace_time' => 15,
        'working_hours' => 8.00,
        'late_after' => 15,
        'half_day_after' => 240,
        'minimum_work_hours' => 8.00,
        'is_flexible' => false,
        'is_night_shift' => false,
        'color' => '#3B82F6',
        'status' => true,
        'created_by' => 1,
        'updated_by' => 1,
    ]);

    $weeklyOffPolicy = WeeklyOffPolicy::create([
        'company_id' => $company->id,
        'policy_name' => 'Corporate Weekend',
        'policy_code' => 'CORP-001',
        'description' => 'Friday and Saturday weekly off.',
        'status' => true,
    ]);

    WeeklyOffPolicyDay::create([
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'day_of_week' => 5,
        'week_type' => 'every',
        'off_type' => 'full_day',
    ]);

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $employee1 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee1->assignRole(User::ROLE_EMPLOYEE);
    $employee1->masterDataItems()->attach([
        $company->id,
        $branch->id,
        $department->id,
        $designation->id,
    ]);
    $employee1->employeeProfile()->create([
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department' => $department->name,
        'designation' => $designation->name,
        'employment_type' => 'probation',
    ]);

    $employee2 = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $employee2->assignRole(User::ROLE_EMPLOYEE);
    $employee2->masterDataItems()->attach([
        $company->id,
        $branch->id,
        $department->id,
        $designation->id,
    ]);
    $employee2->employeeProfile()->create([
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department' => $department->name,
        'designation' => $designation->name,
        'employment_type' => 'probation',
    ]);

    EmployeeShiftAssignment::create([
        'user_id' => $employee2->id,
        'shift_id' => $morningShift->id,
        'effective_from' => now()->subDays(5),
        'effective_to' => null,
        'assignment_type' => 'initial',
        'is_current' => true,
    ]);

    EmployeeWeeklyOffAssignment::create([
        'user_id' => $employee2->id,
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'effective_from' => now()->subDays(5),
        'effective_to' => null,
        'assignment_type' => 'initial',
        'is_current' => true,
    ]);

    $this->actingAs($hr)->post(route('hr.bulk-assignments.store'), [
        'employee_ids' => [$employee1->id, $employee2->id],
        'shift_id' => $generalShift->id,
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'effective_from' => now()->addDay()->toDateString(),
        'assignment_type' => 'initial',
        'remarks' => 'Test bulk assignment.',
    ]);

    expect(EmployeeShiftAssignment::where('user_id', $employee1->id)->where('is_current', true)->count())->toBe(1);
    expect(EmployeeWeeklyOffAssignment::where('user_id', $employee1->id)->where('is_current', true)->count())->toBe(1);

    expect(EmployeeShiftAssignment::where('user_id', $employee2->id)->where('is_current', true)->where('shift_id', $generalShift->id)->count())->toBe(1);
    expect(EmployeeWeeklyOffAssignment::where('user_id', $employee2->id)->where('is_current', true)->count())->toBe(1);

    $oldAssignment = EmployeeShiftAssignment::where('user_id', $employee2->id)
        ->where('is_current', false)
        ->where('shift_id', $morningShift->id)
        ->first();

    expect($oldAssignment)->not->toBeNull();
    expect($oldAssignment->effective_to)->not->toBeNull();
    expect(\Carbon\Carbon::parse($oldAssignment->effective_to)->toDateString())->toBe(now()->addDay()->subDay()->toDateString());
});
