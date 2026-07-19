<?php

namespace App\Services\HR;

use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\User;
use App\Models\WeeklyOffPolicy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeWeeklyOffAssignmentService
{
    /**
     * Assign a weekly off policy to an employee.
     */
    public function store(
        User $employee,
        array $data,
        ?int $userId = null
    ): EmployeeWeeklyOffAssignment {
        return DB::transaction(function () use (
            $employee,
            $data,
            $userId
        ) {
            // Validate business rules (overlaps, etc.)
            $this->validateAssignment($employee, $data);

            // Close any open assignment that overlaps the new start date
            $this->closeCurrentAssignment($employee, $data['effective_from']);

            return EmployeeWeeklyOffAssignment::create([
                'user_id'            => $employee->id,
                'weekly_off_policy_id'   => $data['weekly_off_policy_id'],
                'effective_from'         => $data['effective_from'],
                'effective_to'           => $data['effective_to'] ?? null,
                'assignment_type'        => $data['assignment_type'],
                'remarks'                => $data['remarks'] ?? null,
                'created_by'             => $userId,
                'updated_by'             => $userId,
            ]);
        });
    }

    /**
     * Update an existing assignment.
     */
    public function update(
        EmployeeWeeklyOffAssignment $assignment,
        array $data,
        ?int $userId = null
    ): EmployeeWeeklyOffAssignment {

        return DB::transaction(function () use (
            $assignment,
            $data,
            $userId
        ) {

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
            $this->adjustPreviousAssignment(
                $assignment,
                $newStart
            );

            // Calculate end date from next assignment
            $newEnd = $this->calculateEffectiveTo(
                $assignment,
                $newStart
            );

            $assignment->update([

                'weekly_off_policy_id' => $data['weekly_off_policy_id'],

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
     * Prevent editing historical assignments.
     */
    protected function validateEditable(
        EmployeeWeeklyOffAssignment $assignment
    ): void {

        // Historical records are locked
        if (
            $assignment->effective_to &&
            Carbon::parse($assignment->effective_to)->lt(today())
        ) {

            throw ValidationException::withMessages([
                'effective_from' =>
                'Historical weekly off assignments cannot be edited.',
            ]);
        }
    }


    /**
     * Adjust previous assignment so there is no overlap.
     */
    protected function adjustPreviousAssignment(
        EmployeeWeeklyOffAssignment $assignment,
        Carbon $newStart
    ): void {

        $previous = EmployeeWeeklyOffAssignment::query()

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
        EmployeeWeeklyOffAssignment $assignment,
        Carbon $newStart
    ): ?Carbon {

        $next = EmployeeWeeklyOffAssignment::query()

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
     * Delete (soft delete) an assignment.
     */
    public function delete(EmployeeWeeklyOffAssignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Get available weekly off policies for assignment.
     * For simplicity, return all active policies.
     */
    public function getAvailablePolicies(User $employee)
    {
        return WeeklyOffPolicy::where('status', true)
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);
    }

    /**
     * Validate that the proposed assignment does not conflict with existing ones.
     */
    protected function validateAssignment(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);
        $effectiveTo = $data['effective_to'] ? Carbon::parse($data['effective_to']) : null;

        // Check for any overlapping assignments (excluding those that end before the new start)
        $overlap = EmployeeWeeklyOffAssignment::where('user_id', $employee->id)
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

        $overlap = EmployeeWeeklyOffAssignment::where('user_id', $employee->id)
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
        $assignment = EmployeeWeeklyOffAssignment::where('user_id', $employee->id)
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
                'updated_by'   => auth()->id() ?? null,
            ]);
        }
    }

    /**
     * Change Weekly Off Assignment
     *
     * Previous assignment becomes historical.
     * New assignment becomes current.
     */
    public function changeAssignment(
        EmployeeWeeklyOffAssignment $currentAssignment,
        array $data,
        ?int $userId = null
    ): EmployeeWeeklyOffAssignment {

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

            return EmployeeWeeklyOffAssignment::create([

                'user_id' => $currentAssignment->user_id,

                'weekly_off_policy_id' =>
                $data['weekly_off_policy_id'],

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
