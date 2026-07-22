<?php

namespace App\Services\HR;

use App\Models\EmployeeShiftAssignment;
use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\MasterDataItem;
use App\Models\User;
use App\Models\WeeklyOffPolicy;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkAssignmentService
{
    /**
     * Filter employees based on provided criteria.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function filterEmployees(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $query = User::query()
            ->with([
                'employeeProfile',
                'currentShiftAssignment.shift',
                'currentWeeklyOffAssignment.weeklyOffPolicy',
            ])
            ->whereHas('roles', fn ($roles) => $roles->where('name', User::ROLE_EMPLOYEE))
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['company']), function ($query) use ($filters) {
                $query->whereHas('masterDataItems', function ($md) use ($filters) {
                    $md->where('category', MasterDataItem::CATEGORY_COMPANY)
                        ->where('master_data_items.id', $filters['company']);
                });
            })
            ->when(!empty($filters['branch']), function ($query) use ($filters) {
                $query->whereHas('masterDataItems', function ($md) use ($filters) {
                    $md->where('category', MasterDataItem::CATEGORY_BRANCH)
                        ->where('master_data_items.id', $filters['branch']);
                });
            })
            ->when(!empty($filters['division']), function ($query) use ($filters) {
                $query->whereHas('masterDataItems', function ($md) use ($filters) {
                    $md->where('category', MasterDataItem::CATEGORY_DIVISION)
                        ->where('master_data_items.id', $filters['division']);
                });
            })
            ->when(!empty($filters['department']), function ($query) use ($filters) {
                if (is_numeric($filters['department'])) {
                    $query->whereHas('masterDataItems', function ($md) use ($filters) {
                        $md->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
                            ->where('master_data_items.id', $filters['department']);
                    });
                } else {
                    $query->whereHas('employeeProfile', function ($profile) use ($filters) {
                        $profile->where('department', $filters['department']);
                    });
                }
            })
            ->when(!empty($filters['designation']), function ($query) use ($filters) {
                if (is_numeric($filters['designation'])) {
                    $query->whereHas('masterDataItems', function ($md) use ($filters) {
                        $md->where('category', MasterDataItem::CATEGORY_DESIGNATION)
                            ->where('master_data_items.id', $filters['designation']);
                    });
                } else {
                    $query->whereHas('employeeProfile', function ($profile) use ($filters) {
                        $profile->where('designation', $filters['designation']);
                    });
                }
            })
            ->when(!empty($filters['employment_type']), function ($query) use ($filters) {
                $query->whereHas('employeeProfile', function ($profile) use ($filters) {
                    $profile->where('employment_type', $filters['employment_type']);
                });
            })
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->whereHas('employeeProfile', function ($profile) use ($filters) {
                    $profile->where('employment_status', $filters['status']);
                });
            })
            ->when(!empty($filters['employee_id']), function ($query) use ($filters) {
                $query->where('id', $filters['employee_id']);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $employee) => $this->transformEmployee($employee));

        return $query;
    }

    /**
     * @param  array<int>  $employeeIds
     * @return BulkAssignmentResult
     */
    public function processAssignment(array $employeeIds, array $data, int $userId): BulkAssignmentResult
    {
        $result = new BulkAssignmentResult();
        $result->totalSelected = count($employeeIds);

        foreach ($employeeIds as $employeeId) {
            try {
                $employee = User::findOrFail($employeeId);

                $currentShift = $employee->currentShiftAssignment;
                $currentWeeklyOff = $employee->currentWeeklyOffAssignment;

                $needsShift = (int) ($data['shift_id'] ?? 0) !== 0
                    && (! $currentShift || (int) $currentShift->shift_id !== (int) $data['shift_id']);

                $needsWeeklyOff = (int) ($data['weekly_off_policy_id'] ?? 0) !== 0
                    && (! $currentWeeklyOff || (int) $currentWeeklyOff->weekly_off_policy_id !== (int) $data['weekly_off_policy_id']);

                if (! $needsShift && ! $needsWeeklyOff) {
                    $result->addSkip(
                        $employee->id,
                        'Already assigned to selected shift and weekly off.'
                    );

                    continue;
                }

                DB::transaction(function () use ($employee, $data, $userId, $needsShift, $needsWeeklyOff) {
                    $effectiveFrom = Carbon::parse($data['effective_from'])->startOfDay();

                    if ($needsShift) {
                        $this->assignShift($employee, $data, $userId, $effectiveFrom);
                    }

                    if ($needsWeeklyOff) {
                        $this->assignWeeklyOff($employee, $data, $userId, $effectiveFrom);
                    }
                });

                $result->incrementSuccess();
            } catch (\Throwable $e) {
                $result->addFailure($employeeId, $e->getMessage());
            }
        }

        return $result;
    }

    protected function assignShift(User $employee, array $data, int $userId, Carbon $effectiveFrom): void
    {
        $current = EmployeeShiftAssignment::query()
            ->where('user_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->startOfDay());
            })
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($current) {
            $current->update([
                'effective_to' => $effectiveFrom->copy()->subDay(),
                'is_current' => false,
                'updated_by' => $userId,
            ]);
        }

        EmployeeShiftAssignment::create([
            'user_id' => $employee->id,
            'shift_id' => $data['shift_id'],
            'effective_from' => $effectiveFrom,
            'effective_to' => null,
            'assignment_type' => $data['assignment_type'],
            'remarks' => $data['remarks'] ?? null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    protected function assignWeeklyOff(User $employee, array $data, int $userId, Carbon $effectiveFrom): void
    {
        $current = EmployeeWeeklyOffAssignment::query()
            ->where('user_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->startOfDay());
            })
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($current) {
            $current->update([
                'effective_to' => $effectiveFrom->copy()->subDay(),
                'is_current' => false,
                'updated_by' => $userId,
            ]);
        }

        EmployeeWeeklyOffAssignment::create([
            'user_id' => $employee->id,
            'weekly_off_policy_id' => $data['weekly_off_policy_id'],
            'effective_from' => $effectiveFrom,
            'effective_to' => null,
            'assignment_type' => $data['assignment_type'],
            'remarks' => $data['remarks'] ?? null,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /** @return array<string, array<int, array{id: mixed, label: string}>> */
    public function filterOptions(): array
    {
        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
            ->values()
            ->all();

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
            ->values()
            ->all();

        $divisions = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DIVISION)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
            ->values()
            ->all();

        $departments = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
            ->values()
            ->all();

        $designations = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
            ->values()
            ->all();

        return [
            'companies' => $companies,
            'branches' => $branches,
            'divisions' => $divisions,
            'departments' => $departments,
            'designations' => $designations,
            'employmentTypes' => ['full_time', 'part_time', 'contract', 'intern', 'consultant'],
            'statuses' => array_map(fn ($status) => ['id' => $status, 'label' => ucfirst($status)], [
                \App\Models\EmployeeProfile::STATUS_ONBOARDING,
                \App\Models\EmployeeProfile::STATUS_PROBATION,
                \App\Models\EmployeeProfile::STATUS_ACTIVE,
                \App\Models\EmployeeProfile::STATUS_LEFT,
                \App\Models\EmployeeProfile::STATUS_TERMINATED,
                \App\Models\EmployeeProfile::STATUS_REJOINED,
            ]),
            'shifts' => \App\Models\Shift::query()
                ->where('status', true)
                ->orderBy('shift_name')
                ->get(['id', 'shift_name', 'shift_code'])
                ->map(fn ($shift) => ['id' => $shift->id, 'label' => "{$shift->shift_name} ({$shift->shift_code})"])
                ->values()
                ->all(),
            'weeklyOffPolicies' => WeeklyOffPolicy::query()
                ->where('status', true)
                ->orderBy('policy_name')
                ->get(['id', 'policy_name', 'policy_code'])
                ->map(fn ($policy) => ['id' => $policy->id, 'label' => "{$policy->policy_name} ({$policy->policy_code})"])
                ->values()
                ->all(),
        ];
    }

    protected function transformEmployee(User $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_id' => $employee->employee_id,
            'name' => $employee->name,
            'email' => $employee->email,
            'company' => $employee->masterDataItems->firstWhere('category', MasterDataItem::CATEGORY_COMPANY)?->name,
            'branch' => $employee->masterDataItems->firstWhere('category', MasterDataItem::CATEGORY_BRANCH)?->name,
            'division' => $employee->masterDataItems->firstWhere('category', MasterDataItem::CATEGORY_DIVISION)?->name,
            'department' => $employee->employeeProfile?->department,
            'designation' => $employee->employeeProfile?->designation,
            'employment_type' => $employee->employeeProfile?->employment_type,
            'status' => $employee->employeeProfile?->employment_status,
            'current_shift' => $employee->currentShiftAssignment?->shift?->shift_name,
            'current_weekly_off' => $employee->currentWeeklyOffAssignment?->weeklyOffPolicy?->policy_name,
        ];
    }
}
