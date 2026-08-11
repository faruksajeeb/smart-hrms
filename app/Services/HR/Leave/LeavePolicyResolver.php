<?php

namespace App\Services\HR\Leave;

use App\Models\LeavePolicyAssignment;
use App\Models\LeavePolicy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeavePolicyResolver
{
    /**
     * Priority levels for policy assignment resolution
     * Lower number = higher priority
     */
    private const PRIORITY_LEVELS = [
        'user_specific' => 1,
        'designation_employment_type' => 2,
        'designation' => 3,
        'employment_type' => 4,
        'unit' => 5,
        'section' => 6,
        'department' => 7,
        'division' => 8,
        'branch' => 9,
        'company' => 10,
    ];

    /**
     * Resolve the applicable leave policy for an employee on a specific date.
     * Returns the LeavePolicyAssignment with the highest priority that matches.
     *
     * @param User $employee
     * @param Carbon $date
     * @return LeavePolicyAssignment|null
     */
    public function resolve(User $employee, Carbon $date): ?LeavePolicyAssignment
    {
        $dateOnly = $date->format('Y-m-d');

        // Get all active assignments for the employee's organization on the given date
        $assignments = $this->getMatchingAssignments($employee, $dateOnly);

        if ($assignments->isEmpty()) {
            return null;
        }

        // Group by priority level
        $grouped = $assignments->groupBy('priority');

        // Find the highest priority level that has matches
        $highestPriority = min($grouped->keys()->toArray());

        $candidates = $grouped->get($highestPriority);

        // If multiple candidates at the same priority, return conflict
        if ($candidates->count() > 1) {
            throw new \RuntimeException(
                'Configuration conflict: Multiple leave policy assignments match at priority level ' . $highestPriority .
                '. Please review assignments for employee ' . $employee->employee_id
            );
        }

        return $candidates->first();
    }

    /**
     * Get all matching assignments for an employee on a specific date.
     *
     * @param User $employee
     * @param string $dateOnly
     * @return Collection
     */
    private function getMatchingAssignments(User $employee, string $dateOnly): Collection
    {
        $assignments = collect();

        // 1. User-specific (highest priority)
        $userSpecific = LeavePolicyAssignment::query()
            ->where('status', 'active')
            ->where('effective_from', '<=', $dateOnly)
            ->where(function ($query) use ($dateOnly) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $dateOnly);
            })
            ->where('user_id', $employee->id)
            ->with('policy')
            ->get();
        $assignments = $assignments->merge($this->addPriority($userSpecific, 'user_specific'));

        // 2. Designation + Employment Type
        if ($employee->designation_id && $employee->employment_type_id) {
            $employmentType = \App\Models\MasterDataItem::find($employee->employment_type_id);
            if ($employmentType) {
                $desigEmpType = LeavePolicyAssignment::query()
                    ->where('status', 'active')
                    ->where('effective_from', '<=', $dateOnly)
                    ->where(function ($query) use ($dateOnly) {
                        $query->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $dateOnly);
                    })
                    ->whereNull('user_id')
                    ->where('designation_id', $employee->designation_id)
                    ->where(function ($q) use ($employmentType) {
                        $q->where('employment_type', $employmentType->code)
                            ->orWhere('employment_type', $employmentType->name);
                    })
                    ->with('policy')
                    ->get();
                $assignments = $assignments->merge($this->addPriority($desigEmpType, 'designation_employment_type'));
            }
        }

        // 3. Designation
        if ($employee->designation_id) {
            $designation = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->where('designation_id', $employee->designation_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($designation, 'designation'));
        }

        // 4. Employment Type
        if ($employee->employment_type_id) {
            $employmentType = \App\Models\MasterDataItem::find($employee->employment_type_id);
            if ($employmentType) {
                $empType = LeavePolicyAssignment::query()
                    ->where('status', 'active')
                    ->where('effective_from', '<=', $dateOnly)
                    ->where(function ($query) use ($dateOnly) {
                        $query->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $dateOnly);
                    })
                    ->whereNull('user_id')
                    ->whereNull('designation_id')
                    ->where(function ($q) use ($employmentType) {
                        $q->where('employment_type', $employmentType->code)
                            ->orWhere('employment_type', $employmentType->name);
                    })
                    ->with('policy')
                    ->get();
                $assignments = $assignments->merge($this->addPriority($empType, 'employment_type'));
            }
        }

        // 5. Unit
        if ($employee->unit_id) {
            $unit = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->where('unit_id', $employee->unit_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($unit, 'unit'));
        }

        // 6. Section
        if ($employee->section_id) {
            $section = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->whereNull('unit_id')
                ->where('section_id', $employee->section_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($section, 'section'));
        }

        // 7. Department
        if ($employee->department_id) {
            $department = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->whereNull('unit_id')
                ->whereNull('section_id')
                ->where('department_id', $employee->department_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($department, 'department'));
        }

        // 8. Division
        if ($employee->division_id) {
            $division = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->whereNull('unit_id')
                ->whereNull('section_id')
                ->whereNull('department_id')
                ->where('division_id', $employee->division_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($division, 'division'));
        }

        // 9. Branch
        if ($employee->branch_id) {
            $branch = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->whereNull('unit_id')
                ->whereNull('section_id')
                ->whereNull('department_id')
                ->whereNull('division_id')
                ->where('branch_id', $employee->branch_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($branch, 'branch'));
        }

        // 10. Company (lowest priority)
        if ($employee->company_id) {
            $company = LeavePolicyAssignment::query()
                ->where('status', 'active')
                ->where('effective_from', '<=', $dateOnly)
                ->where(function ($query) use ($dateOnly) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $dateOnly);
                })
                ->whereNull('user_id')
                ->whereNull('employment_type')
                ->whereNull('designation_id')
                ->whereNull('unit_id')
                ->whereNull('section_id')
                ->whereNull('department_id')
                ->whereNull('division_id')
                ->whereNull('branch_id')
                ->where('company_id', $employee->company_id)
                ->with('policy')
                ->get();
            $assignments = $assignments->merge($this->addPriority($company, 'company'));
        }

        return $assignments;
    }

    /**
     * Add priority level to a collection of assignments.
     *
     * @param Collection $assignments
     * @param string $priorityKey
     * @return Collection
     */
    private function addPriority(Collection $assignments, string $priorityKey): Collection
    {
        return $assignments->map(function ($assignment) use ($priorityKey) {
            $assignment->priority = self::PRIORITY_LEVELS[$priorityKey];
            return $assignment;
        });
    }

    /**
     * Get the resolved leave policy for an employee on a specific date.
     *
     * @param User $employee
     * @param Carbon $date
     * @return LeavePolicy|null
     */
    public function getPolicy(User $employee, Carbon $date): ?LeavePolicy
    {
        $assignment = $this->resolve($employee, $date);
        return $assignment?->policy;
    }

    /**
     * Get the leave policy detail for a specific leave type.
     *
     * @param LeavePolicy $policy
     * @param int $leaveTypeId
     * @return \App\Models\LeavePolicyDetail|null
     */
    public function getPolicyDetail(LeavePolicy $policy, int $leaveTypeId): ?\App\Models\LeavePolicyDetail
    {
        return $policy->details()
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 'active')
            ->first();
    }
}