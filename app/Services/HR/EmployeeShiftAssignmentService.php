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

        return DB::transaction(function () use (
            $employee,
            $data,
            $userId
        ) {

            $this->validateAssignment($employee, $data);

            // Prevent duplicate active assignment
            $this->validateDuplicateShift($employee, $data);

            $this->closeCurrentAssignment(
                $employee,
                $data['effective_from']
            );

            return EmployeeShiftAssignment::create([

                'user_id'         => $employee->id,
                'shift_id'        => $data['shift_id'],
                'effective_from'  => $data['effective_from'],
                'effective_to'    => null, // Let timeline manage this
                'assignment_type' => $data['assignment_type'],
                'remarks'         => $data['remarks'] ?? null,
                'created_by'      => $userId,
                'updated_by'      => $userId,

            ]);
        });
    }

    protected function validateDuplicateShift(
        User $employee,
        array $data
    ): void {

        $currentAssignment = EmployeeShiftAssignment::query()

            ->where('user_id', $employee->id)

            ->whereNull('effective_to')

            ->first();

        if (!$currentAssignment) {
            return;
        }

        if (
            $currentAssignment->shift_id == $data['shift_id']
        ) {

            throw ValidationException::withMessages([

                'shift_id' =>

                'The employee is already assigned to this shift.',

            ]);
        }
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

            $newStart = Carbon::parse($data['effective_from']);

            // Historical record lock
            $this->validateEditable($assignment);

            // Timeline overlap validation
            $this->validateAssignmentForUpdate(
                $assignment->employee,
                $data,
                $assignment->id
            );

            // Adjust previous assignment
            $this->adjustPreviousAssignment($assignment, $newStart);

            // Calculate end date from next assignment
            $newEnd = $this->calculateEffectiveTo($assignment, $newStart);

            $assignment->update([
                'shift_id' => $data['shift_id'],
                'effective_from' => $newStart,
                'effective_to' => $newEnd,
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
            ->orderBy('shift_name')
            ->get(['id', 'shift_name', 'shift_code']);
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
                'effective_from' => 'The selected date range overlaps with an existing assignment.',
            ]);
        }

        // Optional: ensure effective_from is not in the past (if business rule)
        // if ($effectiveFrom->isPast()) {
        //     throw ValidationException::withMessages([
        //         'effective_from' => 'Effective date cannot be in the past.',
        //     ]);
        // }
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
     * Close the currently active assignment (if any) before the new effective date.
     * Sets its effective_to to the day before the new effective_from.
     */
    protected function closeCurrentAssignment(
        User $employee,
        string|Carbon $effectiveFrom
    ): void {
        $effectiveFrom = Carbon::parse($effectiveFrom)->startOfDay();

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

    /**
     * Prevent editing historical assignments.
     */
    protected function validateEditable(EmployeeShiftAssignment $assignment): void
    {
        // Historical records are locked
        if (
            $assignment->effective_to &&
            Carbon::parse($assignment->effective_to)->lt(today())
        ) {

            throw ValidationException::withMessages([
                'effective_from' =>
                'Historical shift assignments cannot be edited.',
            ]);
        }
    }


    /**
     * Adjust previous assignment so there is no overlap.
     */
    protected function adjustPreviousAssignment(
        EmployeeShiftAssignment $assignment,
        Carbon $newStart
    ): void {

        $previous = EmployeeShiftAssignment::query()

            ->where('user_id', $assignment->user_id)

            ->where('id', '<>', $assignment->id)

            ->where('effective_from', '<', $newStart)

            ->orderByDesc('effective_from')

            ->first();

        if (!$previous) {
            return;
        }

        $previous->update([

            'effective_to' => $newStart
                ->copy()
                ->subDay(),

            'updated_by' => auth()->id(),

        ]);
    }


    /**
     * Determine effective_to from the next assignment.
     */
    protected function calculateEffectiveTo(
        EmployeeShiftAssignment $assignment,
        Carbon $newStart
    ): ?Carbon {

        $next = EmployeeShiftAssignment::query()

            ->where('user_id', $assignment->user_id)

            ->where('id', '<>', $assignment->id)

            ->where('effective_from', '>', $newStart)

            ->orderBy('effective_from')

            ->first();

        if (!$next) {

            return null;
        }

        return Carbon::parse(
            $next->effective_from
        )->subDay();
    }

    /**
     * Change Shift Assignment
     *
     * Previous assignment becomes historical.
     * New assignment becomes current.
     */
    public function changeAssignment(
        EmployeeShiftAssignment $currentAssignment,
        array $data,
        ?int $userId = null
    ): EmployeeShiftAssignment {

        return DB::transaction(function () use (
            $currentAssignment,
            $data,
            $userId
        ) {

            $newStart = Carbon::parse(
                $data['effective_from']
            )->startOfDay();

            /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

            if ($newStart->lte(
                Carbon::parse(
                    $currentAssignment->effective_from
                )
            )) {

                throw ValidationException::withMessages([

                    'effective_from' =>

                    'Effective date must be after the current assignment start date.',

                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | Close Current Assignment
        |--------------------------------------------------------------------------
        */

            $currentAssignment->update([

                'effective_to' => $newStart
                    ->copy()
                    ->subDay(),

                'updated_by' => $userId,

            ]);

            /*
        |--------------------------------------------------------------------------
        | Create New Assignment
        |--------------------------------------------------------------------------
        */

            return EmployeeShiftAssignment::create([

                'user_id' => $currentAssignment->user_id,

                'shift_id' => $data['shift_id'],

                'effective_from' =>
                $newStart,

                'effective_to' => null,

                'assignment_type' =>
                $data['assignment_type'],

                'remarks' =>
                $data['remarks'] ?? null,

                'created_by' =>
                $userId,

                'updated_by' =>
                $userId,

            ]);
        });
    }
}
