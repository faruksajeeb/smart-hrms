<?php

use App\Models\LeavePolicy;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\MasterDataItem;
use App\Models\User;
use App\Services\HR\Leave\LeavePolicyResolver;
use Carbon\Carbon;
use Database\Seeders\DummyMasterDataItemSeeder;

test('leave policy resolver finds user-specific assignment', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $employee = User::factory()->create();
    $leaveType = LeaveType::factory()->create();
    $policy = LeavePolicy::factory()->create(['status' => 'active']);
    
    $assignment = LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'user_id' => $employee->id,
        'status' => 'active',
        'effective_from' => now()->subMonth()->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $resolver = new LeavePolicyResolver();
    $result = $resolver->resolve($employee, Carbon::now());

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($assignment->id);
});

test('leave policy resolver falls back to designation assignment', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $designation = MasterDataItem::where('category', MasterDataItem::CATEGORY_DESIGNATION)->first();
    $employee = User::factory()->create(['designation_id' => $designation?->id]);
    $leaveType = LeaveType::factory()->create();
    $policy = LeavePolicy::factory()->create(['status' => 'active']);
    
    $assignment = LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'designation_id' => $designation?->id,
        'status' => 'active',
        'effective_from' => now()->subMonth()->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $resolver = new LeavePolicyResolver();
    $result = $resolver->resolve($employee, Carbon::now());

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($assignment->id);
});

test('leave policy resolver throws exception on duplicate assignments', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $designation = MasterDataItem::where('category', MasterDataItem::CATEGORY_DESIGNATION)->first();
    $employee = User::factory()->create(['designation_id' => $designation?->id]);
    $leaveType = LeaveType::factory()->create();
    $policy = LeavePolicy::factory()->create(['status' => 'active']);
    
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'designation_id' => $designation?->id,
        'status' => 'active',
        'effective_from' => now()->subMonth()->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);
    
    LeavePolicyAssignment::create([
        'leave_policy_id' => $policy->id,
        'designation_id' => $designation?->id,
        'status' => 'active',
        'effective_from' => now()->subMonth()->addDay()->format('Y-m-d'),
        'effective_to' => null,
        'created_by' => $employee->id,
        'updated_by' => $employee->id,
    ]);

    $resolver = new LeavePolicyResolver();
    
    expect(fn () => $resolver->resolve($employee, Carbon::now()))
        ->toThrow(\RuntimeException::class, 'Configuration conflict');
});

test('leave policy resolver returns null when no assignment matches', function () {
    $this->seed(DummyMasterDataItemSeeder::class);
    
    $employee = User::factory()->create();
    $resolver = new LeavePolicyResolver();
    
    $result = $resolver->resolve($employee, Carbon::now());
    expect($result)->toBeNull();
});