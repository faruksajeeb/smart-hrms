<?php

namespace App\Services\HR;

use App\Enums\EmploymentMovementType;
use App\Models\EmployeeEmploymentHistory;
use App\Models\MasterDataItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeEmploymentMovementService
{
    public function createInitialAppointment(
        User $employee,
        array $data,
        ?int $userId = null
    ): EmployeeEmploymentHistory {
        return DB::transaction(function () use ($employee, $data, $userId) {
            $history = EmployeeEmploymentHistory::create([
                'user_id' => $employee->id,
                'event_type' => EmploymentMovementType::InitialAppointment->value,
                'company_id' => $data['company_id'] ?? $employee->company_id,
                'branch_id' => $data['branch_id'] ?? $employee->branch_id,
                'cluster_id' => $data['cluster_id'] ?? $employee->cluster_id,
                'division_id' => $data['division_id'] ?? $employee->division_id,
                'department_id' => $data['department_id'] ?? $employee->department_id,
                'section_id' => $data['section_id'] ?? $employee->section_id,
                'unit_id' => $data['unit_id'] ?? $employee->unit_id,
                'designation_id' => $data['designation_id'] ?? $employee->designation_id,
                'employment_type_id' => $data['employment_type_id'] ?? $employee->employment_type_id,
                'reporting_manager_id' => $data['reporting_manager_id'] ?? $employee->reporting_manager_id,
                'effective_from' => $data['effective_from'] ?? $employee->joining_date ?? now()->toDateString(),
                'effective_to' => null,
                'reason' => $data['reason'] ?? null,
                'remarks' => $data['remarks'] ?? 'Initial appointment',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->updateEmployeeMaster($employee, $history, $userId);

            return $history;
        });
    }

    public function createMovement(
        User $employee,
        EmploymentMovementType $type,
        array $data,
        ?int $userId = null
    ): EmployeeEmploymentHistory {
        return DB::transaction(function () use ($employee, $type, $data, $userId) {
            $this->validateMovement($employee, $data);

            $this->closeCurrentHistory($employee, $data['effective_from']);

            $oldValues = $this->getCurrentValues($employee);
            $newValues = $this->normalizeNewValues($data, $oldValues);

            $changes = $this->detectChanges($oldValues, $newValues);

            $history = EmployeeEmploymentHistory::create([
                'user_id' => $employee->id,
                'event_type' => $type->value,
                'company_id' => $newValues['company_id'] ?? $oldValues['company_id'],
                'branch_id' => $newValues['branch_id'] ?? $oldValues['branch_id'],
                'cluster_id' => $newValues['cluster_id'] ?? $oldValues['cluster_id'],
                'division_id' => $newValues['division_id'] ?? $oldValues['division_id'],
                'department_id' => $newValues['department_id'] ?? $oldValues['department_id'],
                'section_id' => $newValues['section_id'] ?? $oldValues['section_id'],
                'unit_id' => $newValues['unit_id'] ?? $oldValues['unit_id'],
                'designation_id' => $newValues['designation_id'] ?? $oldValues['designation_id'],
                'employment_type_id' => $newValues['employment_type_id'] ?? $oldValues['employment_type_id'],
                'reporting_manager_id' => $newValues['reporting_manager_id'] ?? $oldValues['reporting_manager_id'],
                'effective_from' => $data['effective_from'],
                'effective_to' => null,
                'reason' => $data['reason'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'changes' => $changes,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->updateEmployeeMaster($employee, $history, $userId);

            return $history;
        });
    }

    public function validateMovement(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);

        if ($effectiveFrom->lt(today())) {
            throw ValidationException::withMessages([
                'effective_from' => 'Effective date cannot be in the past.',
            ]);
        }

        $this->validateTimeline($employee, $effectiveFrom);
    }

    protected function validateTimeline(
        User $employee,
        Carbon $effectiveFrom
    ): void {
        if ($effectiveFrom->lt(Carbon::parse($employee->joining_date))) {
            throw ValidationException::withMessages([
                'effective_from' => 'Effective date cannot be earlier than the employee joining date.',
            ]);
        }

        $duplicate = EmployeeEmploymentHistory::query()
            ->where('user_id', $employee->id)
            ->whereDate('effective_from', $effectiveFrom)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'effective_from' => 'An employment movement already exists for the selected effective date.',
            ]);
        }

        $current = EmployeeEmploymentHistory::query()
            ->where('user_id', $employee->id)
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();

        if (
            $current &&
            $effectiveFrom->lt($current->effective_from)
        ) {
            throw ValidationException::withMessages([
                'effective_from' => 'Backdated employment movements are not allowed. Please use the Employment History Correction feature.',
            ]);
        }
    }

    protected function closeCurrentHistory(User $employee, string|Carbon $effectiveFrom): void
    {
        $effectiveFrom = Carbon::parse($effectiveFrom)->startOfDay();

        $current = EmployeeEmploymentHistory::where('user_id', $employee->id)
            ->whereNull('effective_to')
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($current) {
            $current->update([
                'effective_to' => $effectiveFrom->copy()->subDay(),
                'updated_by' => auth()->id() ?? null,
            ]);
        }
    }

    protected function getCurrentValues(User $employee): array
    {
        return [
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'cluster_id' => $employee->cluster_id,
            'division_id' => $employee->division_id,
            'department_id' => $employee->department_id,
            'section_id' => $employee->section_id,
            'unit_id' => $employee->unit_id,
            'designation_id' => $employee->designation_id,
            'employment_type_id' => $employee->employment_type_id,
            'reporting_manager_id' => $employee->reporting_manager_id,
        ];
    }

    protected function normalizeNewValues(array $data, array $oldValues): array
    {
        $fields = [
            'company_id',
            'branch_id',
            'cluster_id',
            'division_id',
            'department_id',
            'section_id',
            'unit_id',
            'designation_id',
            'employment_type_id',
            'reporting_manager_id',
        ];

        $result = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            } else {
                $result[$field] = $oldValues[$field] ?? null;
            }
        }

        return $result;
    }

    protected function detectChanges(array $oldValues, array $newValues): array
    {
        $changes = [];
        $fields = [
            'company_id' => 'Company',
            'branch_id' => 'Branch',
            'cluster_id' => 'Cluster',
            'division_id' => 'Division',
            'department_id' => 'Department',
            'section_id' => 'Section',
            'unit_id' => 'Unit',
            'designation_id' => 'Designation',
            'employment_type_id' => 'Employment Type',
            'reporting_manager_id' => 'Reporting Manager',
        ];

        foreach ($fields as $field => $label) {
            $oldId = $oldValues[$field] ?? null;
            $newId = $newValues[$field] ?? null;

            if ($oldId != $newId) {
                $changes[] = [
                    'field' => $field,
                    'label' => $label,
                    'old_value' => $oldId,
                    'new_value' => $newId,
                ];
            }
        }

        return $changes;
    }

    protected function updateEmployeeMaster(
        User $employee,
        EmployeeEmploymentHistory $history,
        ?int $userId = null
    ): void {
        $update = [];

        $fields = [
            'company_id',
            'branch_id',
            'cluster_id',
            'division_id',
            'department_id',
            'section_id',
            'unit_id',
            'designation_id',
            'employment_type_id',
            'reporting_manager_id',
        ];

        foreach ($fields as $field) {
            $newValue = $history->$field;
            if ($newValue !== null && $employee->$field != $newValue) {
                $update[$field] = $newValue;
            }
        }

        if (!empty($update)) {
            $update['updated_by'] = $userId;
            $employee->update($update);
        }
    }

    public function history(User $employee)
    {
        return $this->historyQuery($employee)->get();
    }

    public function historyQuery(User $employee)
    {
        return EmployeeEmploymentHistory::query()
            ->with([
                'company',
                'branch',
                'cluster',
                'division',
                'department',
                'section',
                'unit',
                'designation',
                'employmentType',
                'reportingManager',
                'creator',
                'updater',
            ])
            ->where('user_id', $employee->id);
    }

    public function currentEmployment(User $employee): ?EmployeeEmploymentHistory
    {
        return EmployeeEmploymentHistory::query()
            ->where('user_id', $employee->id)
            ->whereNull('effective_to')
            ->latest('effective_from')
            ->first();
    }
}
