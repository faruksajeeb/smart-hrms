<?php

namespace App\Http\Controllers\HR\Approval;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowLevel;
use App\Models\MasterDataItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class ApprovalWorkflowController extends Controller
{
    public function index(Request $request): Response
    {
        $workflows = ApprovalWorkflow::query()
            ->with('companyBranches')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('HR/Approval/Workflows/Index', [
            'workflows' => $workflows,
        ]);
    }

    public function create(): Response
    {
        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        $branchesByCompany = $branches->groupBy('parent_id');

        return Inertia::render('HR/Approval/Workflows/Create', [
            'companies' => $companies,
            'branches' => $branches,
            'branchesByCompany' => $branchesByCompany,
            'roles' => Role::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'users' => \App\Models\User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_name' => ['required', 'string', 'max:255'],
            'workflow_code' => ['required', 'string', 'max:255', 'unique:approval_workflows,workflow_code'],
            'module_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'company_branches' => ['nullable', 'array'],
            'company_branches.*.company_id' => ['required', 'integer', 'exists:master_data_items,id'],
            'company_branches.*.branch_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'levels' => ['nullable', 'array'],
            'levels.*.level_no' => ['required', 'integer', 'min:1'],
            'levels.*.approval_type' => ['required', 'string', 'in:ROLE,REPORTING_MANAGER,DEPARTMENT_HEAD,BRANCH_MANAGER,HR_MANAGER,COMPANY_ADMIN,SPECIFIC_USER,DYNAMIC'],
            'levels.*.role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'levels.*.specific_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'levels.*.minimum_approvals' => ['required', 'integer', 'min:1'],
            'levels.*.can_reject' => ['boolean'],
            'levels.*.can_delegate' => ['boolean'],
            'levels.*.can_skip' => ['boolean'],
            'levels.*.is_final_level' => ['boolean'],
        ]);

        $workflow = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $workflow = ApprovalWorkflow::create([
                'workflow_name' => $validated['workflow_name'],
                'workflow_code' => $validated['workflow_code'],
                'module_name' => $validated['module_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            if (!empty($validated['levels'])) {
                foreach ($validated['levels'] as $level) {
                    $workflow->levels()->create([
                        'level_no' => $level['level_no'],
                        'approval_type' => $level['approval_type'],
                        'role_id' => $level['role_id'] ?? null,
                        'specific_user_id' => $level['specific_user_id'] ?? null,
                        'minimum_approvals' => $level['minimum_approvals'],
                        'can_reject' => $level['can_reject'] ?? true,
                        'can_delegate' => $level['can_delegate'] ?? false,
                        'can_skip' => $level['can_skip'] ?? false,
                        'is_final_level' => $level['is_final_level'] ?? false,
                        'status' => 'active',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }

            if (!empty($validated['company_branches'])) {
                foreach ($validated['company_branches'] as $companyBranch) {
                    $workflow->companyBranches()->attach($companyBranch['company_id'], [
                        'branch_id' => $companyBranch['branch_id'] ?? null,
                    ]);
                }
            }

            return $workflow;
        });

        return redirect()
            ->route('hr.approval.workflows.index')
            ->with('success', 'Workflow created successfully.');
    }

    public function show(ApprovalWorkflow $workflow): Response
    {
        $workflow->load(['levels', 'companyBranches']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $companyBranchesData = [];
        foreach ($workflow->companyBranches as $company) {
            $branchId = $company->pivot->branch_id;
            $companyId = $company->id;

            if (!isset($companyBranchesData[$companyId])) {
                $companyBranchesData[$companyId] = [
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'code' => $company->code,
                    ],
                    'branch_ids' => [],
                ];
            }

            if ($branchId) {
                $companyBranchesData[$companyId]['branch_ids'][] = $branchId;
            }
        }
        $companyBranchesData = array_values($companyBranchesData);

        return Inertia::render('HR/Approval/Workflows/Show', [
            'workflow' => $workflow,
            'branches' => $branches,
            'companyBranchesData' => $companyBranchesData,
        ]);
    }

    public function edit(ApprovalWorkflow $workflow): Response
    {
        $workflow->load(['levels', 'companyBranches']);

        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'parent_id']);

        $branchesByCompany = $branches->groupBy('parent_id');

        $selectedCompanyBranches = $workflow->companyBranches->map(function ($pivot) {
            return [
                'company_id' => $pivot->id,
                'branch_id' => $pivot->pivot->branch_id,
            ];
        })->values()->all();

        return Inertia::render('HR/Approval/Workflows/Edit', [
            'workflow' => $workflow,
            'companies' => $companies,
            'branches' => $branches,
            'branchesByCompany' => $branchesByCompany,
            'selectedCompanyBranches' => $selectedCompanyBranches,
            'roles' => Role::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'users' => \App\Models\User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
        ]);
    }

    public function update(Request $request, ApprovalWorkflow $workflow)
    {
        $validated = $request->validate([
            'workflow_name' => ['required', 'string', 'max:255'],
            'workflow_code' => ['required', 'string', 'max:255', 'unique:approval_workflows,workflow_code,' . $workflow->id],
            'module_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'company_branches' => ['nullable', 'array'],
            'company_branches.*.company_id' => ['required', 'integer', 'exists:master_data_items,id'],
            'company_branches.*.branch_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'levels' => ['nullable', 'array'],
            'levels.*.id' => ['nullable', 'integer', 'exists:approval_workflow_levels,id'],
            'levels.*.level_no' => ['required', 'integer', 'min:1'],
            'levels.*.approval_type' => ['required', 'string', 'in:ROLE,REPORTING_MANAGER,DEPARTMENT_HEAD,BRANCH_MANAGER,HR_MANAGER,COMPANY_ADMIN,SPECIFIC_USER,DYNAMIC'],
            'levels.*.role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'levels.*.specific_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'levels.*.minimum_approvals' => ['required', 'integer', 'min:1'],
            'levels.*.can_reject' => ['boolean'],
            'levels.*.can_delegate' => ['boolean'],
            'levels.*.can_skip' => ['boolean'],
            'levels.*.is_final_level' => ['boolean'],
            'levels.*._delete' => ['boolean'],
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $workflow, $validated) {
            $workflow->update([
                'workflow_name' => $validated['workflow_name'],
                'workflow_code' => $validated['workflow_code'],
                'module_name' => $validated['module_name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'updated_by' => auth()->id(),
            ]);

            if (array_key_exists('company_branches', $validated)) {
                $workflow->companyBranches()->detach();

                if (!empty($validated['company_branches'])) {
                    foreach ($validated['company_branches'] as $companyBranch) {
                        $workflow->companyBranches()->attach($companyBranch['company_id'], [
                            'branch_id' => $companyBranch['branch_id'] ?? null,
                        ]);
                    }
                }
            }

            if (!empty($validated['levels'])) {
                foreach ($validated['levels'] as $levelData) {
                    if (!empty($levelData['_delete'])) {
                        if (!empty($levelData['id'])) {
                            ApprovalWorkflowLevel::where('id', $levelData['id'])
                                ->where('workflow_id', $workflow->id)
                                ->delete();
                        }
                        continue;
                    }

                    if (!empty($levelData['id'])) {
                        $level = ApprovalWorkflowLevel::where('id', $levelData['id'])
                            ->where('workflow_id', $workflow->id)
                            ->firstOrFail();

                        $level->update([
                            'level_no' => $levelData['level_no'],
                            'approval_type' => $levelData['approval_type'],
                            'role_id' => $levelData['role_id'] ?? null,
                            'specific_user_id' => $levelData['specific_user_id'] ?? null,
                            'minimum_approvals' => $levelData['minimum_approvals'],
                            'can_reject' => $levelData['can_reject'] ?? true,
                            'can_delegate' => $levelData['can_delegate'] ?? false,
                            'can_skip' => $levelData['can_skip'] ?? false,
                            'is_final_level' => $levelData['is_final_level'] ?? false,
                            'updated_by' => auth()->id(),
                        ]);
                    } else {
                        $workflow->levels()->create([
                            'level_no' => $levelData['level_no'],
                            'approval_type' => $levelData['approval_type'],
                            'role_id' => $levelData['role_id'] ?? null,
                            'specific_user_id' => $levelData['specific_user_id'] ?? null,
                            'minimum_approvals' => $levelData['minimum_approvals'],
                            'can_reject' => $levelData['can_reject'] ?? true,
                            'can_delegate' => $levelData['can_delegate'] ?? false,
                            'can_skip' => $levelData['can_skip'] ?? false,
                            'is_final_level' => $levelData['is_final_level'] ?? false,
                            'status' => 'active',
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('hr.approval.workflows.show', $workflow)
            ->with('success', 'Workflow updated successfully.');
    }

    public function destroy(ApprovalWorkflow $workflow)
    {
        $workflow->delete();

        return back()->with('success', 'Workflow deleted successfully.');
    }
}
