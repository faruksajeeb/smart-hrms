<?php

use App\Models\EmployeeLifecycleEvent;
use App\Models\EmployeeProfile;
use App\Models\EmployeeShiftAssignment;
use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\MasterDataItem;
use App\Models\User;
use Database\Seeders\DummyMasterDataItemSeeder;
use Database\Seeders\ShiftSeeder;
use Database\Seeders\WeeklyOffPolicySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('hr users can onboard terminate and rejoin employees', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    $department = MasterDataItem::where('code', 'REC')->firstOrFail();
    $designation = MasterDataItem::where('code', 'HR-OFF')->firstOrFail();
    $employmentType = MasterDataItem::where('code', 'PROB')->firstOrFail();
    $branch = MasterDataItem::where('code', 'DHK')->firstOrFail();
    $bank = MasterDataItem::where('code', 'BRAC')->firstOrFail();
    $payType = MasterDataItem::where('code', 'MON')->firstOrFail();

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->post(route('hr.employees.store'), [
        'name' => 'New Employee',
        'email' => 'employee@example.com',
        'employee_id' => 'EMP-900',
        'employment_status' => EmployeeProfile::STATUS_PROBATION,
        'department_master_data_id' => $department->id,
        'designation_master_data_id' => $designation->id,
        'employment_type_master_data_id' => $employmentType->id,
        'branch_master_data_id' => $branch->id,
        'bank_master_data_id' => $bank->id,
        'pay_type_master_data_id' => $payType->id,
        'joining_date' => '2026-06-10',
        'probation_starts_on' => '2026-06-10',
        'probation_ends_on' => '2026-09-10',
        'probation_status' => EmployeeProfile::PROBATION_PENDING,
        'confirmation_date' => null,
        'leave_policy_name' => 'Standard',
        'annual_leave_days' => 18,
        'sick_leave_days' => 10,
        'casual_leave_days' => 7,
        'carry_forward_leave_days' => 5,
        'salary_amount' => 50000,
        'salary_currency' => 'BDT',
        'bank_account_number' => '123456789',
        'tax_identifier' => 'TIN-123',
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_phone' => '01700000000',
        'notes' => 'First onboarding batch.',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $employee = User::where('email', 'employee@example.com')->firstOrFail();

    expect($employee->hasRole(User::ROLE_EMPLOYEE))->toBeTrue();
    expect($employee->employeeProfile->employment_status)->toBe(EmployeeProfile::STATUS_PROBATION);
    expect($employee->employeeProfile->department)->toBe('Recruitment Department');
    expect($employee->employeeProfile->designation)->toBe('HR Officer');
    expect($employee->employeeProfile->work_location)->toBe('Dhaka Branch');
    expect($employee->employeeProfile->bank_name)->toBe('BRAC Bank');
    expect($employee->employeeLifecycleEvents()->where('type', EmployeeLifecycleEvent::TYPE_ONBOARDING)->exists())->toBeTrue();
    expect($employee->masterDataItems()->whereKey($department->id)->exists())->toBeTrue();
    expect($employee->masterDataItems()->whereKey($designation->id)->exists())->toBeTrue();
    expect($employee->masterDataItems()->where('category', MasterDataItem::CATEGORY_COMPANY)->exists())->toBeTrue();

    $this->actingAs($hr)->post(route('hr.employees.terminate', $employee), [
        'employment_status' => EmployeeProfile::STATUS_TERMINATED,
        'termination_date' => '2026-12-01',
        'termination_type' => 'Dismissal',
        'termination_reason' => 'Policy violation.',
    ])->assertRedirect(route('hr.employees.show', $employee, absolute: false));

    $employee->refresh();
    expect($employee->status)->toBe(User::STATUS_INACTIVE);
    expect($employee->employeeProfile->employment_status)->toBe(EmployeeProfile::STATUS_TERMINATED);

    $this->actingAs($hr)->post(route('hr.employees.rejoin', $employee), [
        'last_rejoined_on' => '2027-01-15',
        'department' => 'Operations',
        'designation' => 'Senior Coordinator',
        'notes' => 'Approved by HR.',
    ])->assertRedirect(route('hr.employees.show', $employee, absolute: false));

    $employee->refresh();
    expect($employee->status)->toBe(User::STATUS_ACTIVE);
    expect($employee->employeeProfile->employment_status)->toBe(EmployeeProfile::STATUS_REJOINED);
    expect($employee->employeeLifecycleEvents()->where('type', EmployeeLifecycleEvent::TYPE_REJOIN)->exists())->toBeTrue();
});

test('hr users can onboard employees with shift and weekly off', function () {
    $this->seed(DummyMasterDataItemSeeder::class);

    Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });

    DB::table('companies')->insert(['id' => 1, 'created_at' => now(), 'updated_at' => now()]);

    $systemUser = User::factory()->create(['id' => 1, 'status' => User::STATUS_ACTIVE]);

    $this->seed(ShiftSeeder::class);
    $this->seed(WeeklyOffPolicySeeder::class);

    $department = MasterDataItem::where('code', 'REC')->firstOrFail();
    $designation = MasterDataItem::where('code', 'HR-OFF')->firstOrFail();
    $employmentType = MasterDataItem::where('code', 'PROB')->firstOrFail();
    $branch = MasterDataItem::where('code', 'DHK')->firstOrFail();
    $bank = MasterDataItem::where('code', 'BRAC')->firstOrFail();
    $payType = MasterDataItem::where('code', 'MON')->firstOrFail();

    $shift = \App\Models\Shift::where('shift_code', 'GEN')->firstOrFail();
    $weeklyOffPolicy = \App\Models\WeeklyOffPolicy::where('policy_code', 'CORP-001')->firstOrFail();

    $hr = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->post(route('hr.employees.store'), [
        'name' => 'Employee With Shift',
        'email' => 'employee-shift@example.com',
        'employee_id' => 'EMP-901',
        'employment_status' => EmployeeProfile::STATUS_ONBOARDING,
        'department_master_data_id' => $department->id,
        'designation_master_data_id' => $designation->id,
        'employment_type_master_data_id' => $employmentType->id,
        'branch_master_data_id' => $branch->id,
        'bank_master_data_id' => $bank->id,
        'pay_type_master_data_id' => $payType->id,
        'shift_id' => $shift->id,
        'weekly_off_policy_id' => $weeklyOffPolicy->id,
        'joining_date' => '2026-07-10',
        'probation_starts_on' => '2026-07-10',
        'probation_ends_on' => '2026-10-10',
        'probation_status' => EmployeeProfile::PROBATION_PENDING,
        'confirmation_date' => null,
        'leave_policy_name' => 'Standard',
        'annual_leave_days' => 18,
        'sick_leave_days' => 10,
        'casual_leave_days' => 7,
        'carry_forward_leave_days' => 5,
        'salary_amount' => 50000,
        'salary_currency' => 'BDT',
        'bank_account_number' => '123456789',
        'tax_identifier' => 'TIN-123',
        'emergency_contact_name' => 'Emergency Contact',
        'emergency_contact_phone' => '01700000000',
        'notes' => 'With shift and weekly off.',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $employee = User::where('email', 'employee-shift@example.com')->firstOrFail();

    expect($employee->hasRole(User::ROLE_EMPLOYEE))->toBeTrue();
    expect($employee->employeeProfile->joining_date->toDateString())->toBe('2026-07-10');

    $shiftAssignment = $employee->shiftAssignments()->where('is_current', true)->first();
    expect($shiftAssignment)->not->toBeNull();
    expect($shiftAssignment->shift_id)->toBe($shift->id);
    expect($shiftAssignment->effective_from)->toBe('10-07-2026');
    expect($shiftAssignment->assignment_type)->toBe('initial');

    $weeklyOffAssignment = $employee->weeklyOffAssignments()->where('is_current', true)->first();
    expect($weeklyOffAssignment)->not->toBeNull();
    expect($weeklyOffAssignment->weekly_off_policy_id)->toBe($weeklyOffPolicy->id);
    expect($weeklyOffAssignment->effective_from)->toBe('10-07-2026');
    expect($weeklyOffAssignment->assignment_type)->toBe('initial');

    $this->actingAs($hr)->get(route('hr.employees.show', $employee))
        ->assertOk();
});

test('hr can read a CV and receive onboarding suggestions', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);
    $department = MasterDataItem::create([
        'category' => MasterDataItem::CATEGORY_DEPARTMENT,
        'code' => 'ENG',
        'name' => 'Engineering',
        'status' => MasterDataItem::STATUS_ACTIVE,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($hr)->post(route('hr.employees.cv-extract'), [
        'cv' => UploadedFile::fake()->createWithContent('candidate.txt', "Name: CV Candidate\nEmail: candidate@example.com\nPhone: 01700000000\nDepartment: Engineering\nSkills\nPHP, Laravel"),
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'CV Candidate')
        ->assertJsonPath('data.skills', 'PHP, Laravel')
        ->assertJsonPath('master_data.department_master_data_id.id', $department->id);
});
