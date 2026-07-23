<?php

namespace App\Services\HR;

use App\Models\EmployeeReportingManagerAssignment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeReportingManagerAssignmentService
{
    /**
     * Assign a reporting manager to an employee.
     */
    public function store(
        User $employee,
        array $data,
        ?int $userId = null
    ): EmployeeReportingManagerAssignment {
        return DB::transaction(function () use (
            $employee,
            $data,
            $userId
        ) {
            $this->validateAssignment($employee, $data);
            $this->validateDuplicateManager($employee, $data);
            $this->validateCircularReporting($employee->id, $data['manager_id']);

            $this->closeCurrentAssignment($employee, $data['effective_from']);

            return EmployeeReportingManagerAssignment::create([
                'user_id'         => $employee->id,
                'manager_id'      => $data['manager_id'],
                'effective_from'  => $data['effective_from'],
                'effective_to'    => null,
                'assignment_type' => $data['assignment_type'],
                'remarks'         => $data['remarks'] ?? null,
                'created_by'      => $userId,
                'updated_by'      => $userId,
            ]);
        });
    }

    /**
     * Change the reporting manager assignment.
     */
    public function changeAssignment(
        EmployeeReportingManagerAssignment $currentAssignment,
        array $data,
        ?int $userId = null
    ): EmployeeReportingManagerAssignment {
        return DB::transaction(function () use (
            $currentAssignment,
            $data,
            $userId
        ) {
            $newStart = Carbon::parse($data['effective_from'])->startOfDay();

            if ($newStart->lte(
                Carbon::parse($currentAssignment->effective_from)
            )) {
                throw ValidationException::withMessages([
                    'effective_from' => 'Effective date must be after the current assignment start date.',
                ]);
            }

            $this->validateCircularReporting(
                $currentAssignment->user_id,
                $data['manager_id']
            );

            $currentAssignment->update([
                'effective_to' => $newStart->copy()->subDay(),
                'is_current' => false,
                'updated_by'   => $userId,
            ]);

            return EmployeeReportingManagerAssignment::create([
                'user_id'         => $currentAssignment->user_id,
                'manager_id'      => $data['manager_id'],
                'effective_from'  => $newStart,
                'effective_to'    => null,
                'assignment_type' => $data['assignment_type'],
                'remarks'         => $data['remarks'] ?? null,
                'created_by'      => $userId,
                'updated_by'      => $userId,
            ]);
        });
    }

    /**
     * Validate that the proposed assignment does not conflict with existing ones.
     */
    protected function validateAssignment(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);
        $effectiveTo = $data['effective_to'] ?? null;
        if ($effectiveTo) {
            $effectiveTo = Carbon::parse($effectiveTo);
        }

        $overlap = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
            ->where(function ($query) use ($effectiveFrom, $effectiveTo) {
                $query->where('effective_from', '<=', $effectiveTo ?? PHP_INT_MAX)
                    ->where(function ($q) use ($effectiveFrom) {
                        $q->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $effectiveFrom);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'The selected date range overlaps with an existing assignment.',
            ]);
        }
    }

    /**
     * Prevent duplicate active manager assignment.
     */
    protected function validateDuplicateManager(User $employee, array $data): void
    {
        $currentAssignment = EmployeeReportingManagerAssignment::query()
            ->where('user_id', $employee->id)
            ->whereNull('effective_to')
            ->first();

        if (!$currentAssignment) {
            return;
        }

        if ($currentAssignment->manager_id == $data['manager_id']) {
            throw ValidationException::withMessages([
                'manager_id' => 'The employee is already assigned to this manager.',
            ]);
        }

        if ($currentAssignment) {
            throw ValidationException::withMessages([
                'employee_id' => 'This employee already has an active reporting manager assignment.',
            ]);
        }
    }

    /**
     * Prevent circular reporting relationships.
     */
    protected function validateCircularReporting(int $employeeId, int $managerId): void
    {
        if ($employeeId === $managerId) {
            throw ValidationException::withMessages([
                'manager_id' => 'An employee cannot be their own reporting manager.',
            ]);
        }

        $visited = [];
        $currentManagerId = $managerId;

        while ($currentManagerId) {
            if ($currentManagerId === $employeeId) {
                throw ValidationException::withMessages([
                    'manager_id' => 'Circular reporting detected. This manager reports to the selected employee.',
                ]);
            }

            if (isset($visited[$currentManagerId])) {
                break;
            }

            $visited[$currentManagerId] = true;

            $currentAssignment = EmployeeReportingManagerAssignment::query()
                ->where('user_id', $currentManagerId)
                ->whereNull('effective_to')
                ->first();

            $currentManagerId = $currentAssignment?->manager_id;
        }
    }

    /**
     * Close the currently active assignment before the new effective date.
     */
    protected function closeCurrentAssignment(
        User $employee,
        string|Carbon $effectiveFrom
    ): void {
        $effectiveFrom = Carbon::parse($effectiveFrom)->startOfDay();

        $assignment = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->startOfDay());
            })
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($assignment) {
            $previousDay = $effectiveFrom->copy()->subDay();
            $assignment->update([
                'effective_to' => $previousDay,
                'updated_by'   => auth()->id() ?? null,
            ]);
        }
    }

    /**
     * Update an existing assignment.
     */
    public function update(
        EmployeeReportingManagerAssignment $assignment,
        array $data,
        ?int $userId = null
    ): EmployeeReportingManagerAssignment {
        return DB::transaction(function () use ($assignment, $data, $userId) {
            $newStart = Carbon::parse($data['effective_from']);

            $this->validateEditable($assignment);

            $this->validateAssignmentForUpdate(
                $assignment->employee,
                $data,
                $assignment->id
            );

            $this->adjustPreviousAssignment($assignment, $newStart);

            $newEnd = $this->calculateEffectiveTo($assignment, $newStart);

            $assignment->update([
                'manager_id'      => $data['manager_id'],
                'effective_from'  => $newStart,
                'effective_to'    => $newEnd,
                'assignment_type' => $data['assignment_type'],
                'remarks'         => $data['remarks'] ?? null,
                'updated_by'      => $userId,
            ]);

            return $assignment->fresh();
        });
    }

    /**
     * Prevent editing historical assignments.
     */
    protected function validateEditable(EmployeeReportingManagerAssignment $assignment): void
    {
        if (
            $assignment->effective_to &&
            Carbon::parse($assignment->effective_to)->lt(today())
        ) {
            throw ValidationException::withMessages([
                'effective_from' => 'Historical reporting manager assignments cannot be edited.',
            ]);
        }
    }

    /**
     * Validate update request, ignoring the assignment being updated.
     */
    protected function validateAssignmentForUpdate(User $employee, array $data, int $assignmentId): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);
        $effectiveTo = $data['effective_to'] ? Carbon::parse($data['effective_to']) : null;

        $overlap = EmployeeReportingManagerAssignment::where('user_id', $employee->id)
            ->where('id', '<>', $assignmentId)
            ->where(function ($query) use ($effectiveFrom, $effectiveTo) {
                $query->where('effective_from', '<=', $effectiveTo ?? PHP_INT_MAX)
                    ->where(function ($q) use ($effectiveFrom) {
                        $q->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $effectiveFrom);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'The selected date range overlaps with another assignment.',
            ]);
        }
    }

    /**
     * Adjust previous assignment so there is no overlap.
     */
    protected function adjustPreviousAssignment(
        EmployeeReportingManagerAssignment $assignment,
        Carbon $newStart
    ): void {
        $previous = EmployeeReportingManagerAssignment::query()
            ->where('user_id', $assignment->user_id)
            ->where('id', '<>', $assignment->id)
            ->where('effective_from', '<', $newStart)
            ->orderByDesc('effective_from')
            ->first();

        if (!$previous) {
            return;
        }

        $previous->update([
            'effective_to' => $newStart->copy()->subDay(),
            'updated_by'   => auth()->id(),
        ]);
    }

    /**
     * Determine effective_to from the next assignment.
     */
    protected function calculateEffectiveTo(
        EmployeeReportingManagerAssignment $assignment,
        Carbon $newStart
    ): ?Carbon {
        $next = EmployeeReportingManagerAssignment::query()
            ->where('user_id', $assignment->user_id)
            ->where('id', '<>', $assignment->id)
            ->where('effective_from', '>', $newStart)
            ->orderBy('effective_from')
            ->first();

        if (!$next) {
            return null;
        }

        return Carbon::parse($next->effective_from)->subDay();
    }

    /**
     * Delete (soft delete) an assignment.
     */
    public function delete(EmployeeReportingManagerAssignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Get assignment history for an employee.
     */
    public function history(User $employee)
    {
        return EmployeeReportingManagerAssignment::query()
            ->with(['manager', 'creator'])
            ->where('user_id', $employee->id)
            ->orderBy('effective_from')
            ->get();
    }

    /**
     * Get available managers for assignment.
     */
    public function getAvailableManagers(User $employee)
    {
        return User::query()
            ->where('id', '!=', $employee->id)
            ->whereHas('roles', fn ($roles) => $roles->where('name', User::ROLE_EMPLOYEE))
            ->orderBy('name')
            ->get(['id', 'employee_id', 'name']);
    }
}
