<?php

use App\Enums\ApprovalStatus;
use App\Enums\ApprovalWorkflowStatus;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Database\Seeders\DummyMasterDataItemSeeder;

beforeEach(function () {
    $this->seed(DummyMasterDataItemSeeder::class);
});

test('hr users can view approval workflows index page', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->get(route('hr.approval.workflows.index'))
        ->assertOk();
});

test('hr users can create an approval workflow', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $this->actingAs($hr)->post(route('hr.approval.workflows.store'), [
        'workflow_name' => 'Test Workflow',
        'workflow_code' => 'TEST_WORKFLOW',
        'module_name' => 'employment_movement',
        'status' => 'active',
    ])->assertRedirect();

    $workflow = ApprovalWorkflow::where('workflow_code', 'TEST_WORKFLOW')->first();

    expect($workflow)->not->toBeNull();
    expect($workflow->status)->toBe(ApprovalWorkflowStatus::Active);
});

test('approval engine can submit a request', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $workflow = ApprovalWorkflow::create([
        'workflow_name' => 'Test Workflow',
        'workflow_code' => 'TEST_WORKFLOW',
        'module_name' => 'employment_movement',
        'status' => 'active',
        'created_by' => $hr->id,
        'updated_by' => $hr->id,
    ]);

    $employee = User::factory()->create(['status' => User::STATUS_ACTIVE]);

    $request = app(\App\Services\HR\Approval\ApprovalEngineService::class)->submit(
        $employee,
        'employment_movement',
        'App\Models\EmployeeEmploymentHistory',
        1,
        $workflow->id
    );

    expect($request)->not->toBeNull();
    expect($request->current_status)->toBe(ApprovalStatus::Pending);
    expect($request->steps)->toHaveCount(0);
});

test('hr users can create an approval workflow with multiple companies and branches', function () {
    $hr = User::factory()->create(['status' => User::STATUS_ACTIVE]);
    $hr->assignRole(User::ROLE_HR);

    $company1 = \App\Models\MasterDataItem::create([
        'category' => \App\Models\MasterDataItem::CATEGORY_COMPANY,
        'status' => \App\Models\MasterDataItem::STATUS_ACTIVE,
        'name' => 'Company 1',
        'code' => 'C1',
    ]);

    $company2 = \App\Models\MasterDataItem::create([
        'category' => \App\Models\MasterDataItem::CATEGORY_COMPANY,
        'status' => \App\Models\MasterDataItem::STATUS_ACTIVE,
        'name' => 'Company 2',
        'code' => 'C2',
    ]);

    $branch1 = \App\Models\MasterDataItem::create([
        'category' => \App\Models\MasterDataItem::CATEGORY_BRANCH,
        'status' => \App\Models\MasterDataItem::STATUS_ACTIVE,
        'name' => 'Branch 1',
        'code' => 'B1',
        'parent_id' => $company1->id,
    ]);

    $branch2 = \App\Models\MasterDataItem::create([
        'category' => \App\Models\MasterDataItem::CATEGORY_BRANCH,
        'status' => \App\Models\MasterDataItem::STATUS_ACTIVE,
        'name' => 'Branch 2',
        'code' => 'B2',
        'parent_id' => $company1->id,
    ]);

    $branch3 = \App\Models\MasterDataItem::create([
        'category' => \App\Models\MasterDataItem::CATEGORY_BRANCH,
        'status' => \App\Models\MasterDataItem::STATUS_ACTIVE,
        'name' => 'Branch 3',
        'code' => 'B3',
        'parent_id' => $company2->id,
    ]);

    $this->actingAs($hr)->post(route('hr.approval.workflows.store'), [
        'workflow_name' => 'Multi Company Workflow',
        'workflow_code' => 'MULTI_COMPANY_WORKFLOW',
        'module_name' => 'employment_movement',
        'status' => 'active',
        'company_branches' => [
            ['company_id' => $company1->id, 'branch_id' => $branch1->id],
            ['company_id' => $company1->id, 'branch_id' => $branch2->id],
            ['company_id' => $company2->id, 'branch_id' => null],
        ],
    ])->assertRedirect();

    $workflow = ApprovalWorkflow::where('workflow_code', 'MULTI_COMPANY_WORKFLOW')->first();

    expect($workflow)->not->toBeNull();
    expect($workflow->companyBranches)->toHaveCount(3);

    $company1Branches = $workflow->companyBranches->where('id', $company1->id);
    expect($company1Branches)->toHaveCount(2);

    $company2Branches = $workflow->companyBranches->where('id', $company2->id);
    expect($company2Branches)->toHaveCount(1);
    expect($company2Branches->first()->pivot->branch_id)->toBeNull();
});
