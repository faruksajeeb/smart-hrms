<?php

namespace App\Services\HR;

use App\Models\EmployeeShiftAssignment;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeShiftAssignmentService
{
    /**
     * Assign a shift to an employee.
     */
    public function store(
        User $employee,
        array $data,
        ?int $userId = null
    ): EmployeeShiftAssignment {
        return DB::transaction(function () use ($employee, $data, $userId) {
            // Validate business rules (no overlapping assignments)
            $this->validateAssignment($employee, $data);

            // If there is a current assignment that is effective and overlaps, we may need to end it.
            // For simplicity, we assume that assigning a new shift replaces the current one from the effective date.
            // We'll set the end date of the current assignment to the day before the new start date.
            $this->rescheduleCurrentAssignment($employee, $data['effective_from']);

            return EmployeeShiftAssignment::create([
                'user_id' => $employee->id,
                'shift_id' => $data['shift_id'],
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'assignment_type' => $data['assignment_type'],
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    /**
     * Update an existing assignment.
     */
    public function update(
        EmployeeShiftAssignment $assignment,
        array $data,
        ?int $userId = null
    ): EmployeeShiftAssignment {
        return DB::transaction(function () use ($assignment, $data, $userId) {
            // Validate business rules, ignoring the current assignment for overlap checks
            $this->validateAssignmentForUpdate($assignment->employee, $data, $assignment->id);

            // If the effective date is changing, we may need to adjust surrounding assignments
            if ($assignment->getOriginal('effective_from') !== $data['effective_from']) {
                $this->rescheduleCurrentAssignment($assignment->employee, $data['effective_from']);
            }

            $assignment->update([
                'shift_id' => $data['shift_id'],
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'assignment_type' => $data['assignment_type'],
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => $userId,
            ]);

            return $assignment->fresh();
        });
    }

    /**
     * Delete (soft delete) an assignment.
     */
    public function delete(EmployeeShiftAssignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Get available shifts for assignment.
     * For simplicity, return all active shifts.
     */
    public function getAvailableShifts(User $employee)
    {
        return Shift::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Validate that the proposed assignment does not conflict with existing ones.
     */
    protected function validateAssignment(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);
        $effectiveTo = $data['effective_to'] ? Carbon::parse($data['effective_to']) : null;

        // Check for any overlapping assignments (excluding those that end before the new start)
        $overlap = EmployeeShiftAssignment::where('user_id', $employee->id)
            ->where(function ($query) use ($effectiveFrom, $effectiveTo) {
                // Existing assignment starts before the new end and ends after the new start
                $query->where('effective_from', '<=', $effectiveTo ?? PHP_INT_MAX)
                    ->where(function ($q) use ($effectiveFrom) {
                        $q->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $effectiveFrom);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'The selected date range overlaps with an existing shift assignment.',
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

        $overlap = EmployeeShiftAssignment::where('user_id', $employee->id)
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
     * End the currently active assignment (if any) before the new effective date.
     * Sets its effective_to to the day before the new effective_from.
     */
    protected function rescheduleCurrentAssignment(User $employee, Carbon $effectiveFrom): void
    {
        $effectiveFrom = $effectiveFrom->copy()->startOfDay();

        // Find the assignment that is currently effective (no end date or end date >= today)
        // and whose effective_from is before the new effective_from.
        $assignment = EmployeeShiftAssignment::where('user_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->startOfDay());
            })
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($assignment) {
            // Set end date to yesterday relative to new start
            $previousDay = $effectiveFrom->copy()->subDay();
            $assignment->update([
                'effective_to' => $previousDay,
                'updated_by' => auth()->id() ?? null,
            ]);
        }
    }
}