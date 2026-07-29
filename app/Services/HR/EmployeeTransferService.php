<?php

namespace App\Services\HR;

use App\Models\EmployeeTransfer;
use App\Models\MasterDataItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeTransferService
{
    public function store(User $employee, array $data, ?int $userId = null): EmployeeTransfer
    {
        return DB::transaction(function () use ($employee, $data, $userId) {
            $this->validateTransfer($employee, $data);

            $this->closeCurrentTransfer($employee, $data['effective_from']);

            return EmployeeTransfer::create([
                'user_id' => $employee->id,
                'from_company_id' => $employee->company_id,
                'from_branch_id' => $employee->branch_id,
                'from_cluster_id' => $employee->cluster_id,
                'from_division_id' => $employee->division_id,
                'from_department_id' => $employee->department_id,
                'from_section_id' => $employee->section_id,
                'from_unit_id' => $employee->unit_id,
                'to_company_id' => $data['to_company_id'] ?? $employee->company_id,
                'to_branch_id' => $data['to_branch_id'] ?? $employee->branch_id,
                'to_cluster_id' => $data['to_cluster_id'] ?? $employee->cluster_id,
                'to_division_id' => $data['to_division_id'] ?? $employee->division_id,
                'to_department_id' => $data['to_department_id'] ?? $employee->department_id,
                'to_section_id' => $data['to_section_id'] ?? $employee->section_id,
                'to_unit_id' => $data['to_unit_id'] ?? $employee->unit_id,
                'effective_from' => $data['effective_from'],
                'effective_to' => null,
                'transfer_reason' => $data['transfer_reason'],
                'remarks' => $data['remarks'] ?? null,
                'approval_status' => $data['approval_status'] ?? 'draft',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }

    public function update(
        EmployeeTransfer $transfer,
        array $data,
        ?int $userId = null
    ): EmployeeTransfer {
        return DB::transaction(function () use ($transfer, $data, $userId) {
            $newStart = Carbon::parse($data['effective_from']);

            $this->validateEditable($transfer);

            $this->validateTransferForUpdate($transfer, $data);

            $this->adjustPreviousTransfer($transfer, $newStart);

            $newEnd = $this->calculateEffectiveTo($transfer, $newStart);

            $transfer->update([
                'to_company_id' => $data['to_company_id'] ?? $transfer->to_company_id,
                'to_branch_id' => $data['to_branch_id'] ?? $transfer->to_branch_id,
                'to_cluster_id' => $data['to_cluster_id'] ?? $transfer->to_cluster_id,
                'to_division_id' => $data['to_division_id'] ?? $transfer->to_division_id,
                'to_department_id' => $data['to_department_id'] ?? $transfer->to_department_id,
                'to_section_id' => $data['to_section_id'] ?? $transfer->to_section_id,
                'to_unit_id' => $data['to_unit_id'] ?? $transfer->to_unit_id,
                'effective_from' => $newStart,
                'effective_to' => $newEnd,
                'transfer_reason' => $data['transfer_reason'],
                'remarks' => $data['remarks'] ?? null,
                'updated_by' => $userId,
            ]);

            return $transfer->fresh();
        });
    }

    public function delete(EmployeeTransfer $transfer): void
    {
        $transfer->delete();
    }

    public function getAvailableItems(string $category, ?string $parentCode = null)
    {
        $query = MasterDataItem::query()
            ->where('category', $category)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name');

        if ($parentCode) {
            $query->where('parent_code', $parentCode);
        }

        return $query->get(['id', 'code', 'name', 'parent_code']);
    }

    protected function validateTransfer(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);

        $current = $employee->currentTransfer;

        if ($current && $current->approval_status === EmployeeTransfer::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'employee_id' => 'This employee already has an approved transfer in progress.',
            ]);
        }

        if ($effectiveFrom->lt(today())) {
            throw ValidationException::withMessages([
                'effective_from' => 'Effective date cannot be in the past.',
            ]);
        }

        $this->validateDuplicateTransfer($employee, $data);
        $this->validateSameTransfer($employee, $data);
    }

    protected function validateTransferForUpdate(EmployeeTransfer $transfer, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);

        if ($transfer->approval_status === EmployeeTransfer::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'effective_from' => 'Approved transfers cannot be modified.',
            ]);
        }

        if ($effectiveFrom->lt(today())) {
            throw ValidationException::withMessages([
                'effective_from' => 'Effective date cannot be in the past.',
            ]);
        }

        $this->validateSameTransfer($transfer->employee, $data, $transfer->id);
    }

    protected function validateDuplicateTransfer(User $employee, array $data): void
    {
        $effectiveFrom = Carbon::parse($data['effective_from']);

        $exists = EmployeeTransfer::where('user_id', $employee->id)
            ->where('approval_status', '!=', 'rejected')
            ->where(function ($query) use ($effectiveFrom) {
                $query->where('effective_from', $effectiveFrom)
                    ->orWhere(function ($q) use ($effectiveFrom) {
                        $q->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', $effectiveFrom);
                    });
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'effective_from' => 'The selected effective date overlaps with an existing transfer.',
            ]);
        }
    }

    protected function validateSameTransfer(User $employee, array $data, ?int $excludeId = null): void
    {
        $toCompanyId = $data['to_company_id'] ?? $employee->company_id;
        $toBranchId = $data['to_branch_id'] ?? $employee->branch_id;
        $toClusterId = $data['to_cluster_id'] ?? $employee->cluster_id;
        $toDivisionId = $data['to_division_id'] ?? $employee->division_id;
        $toDepartmentId = $data['to_department_id'] ?? $employee->department_id;
        $toSectionId = $data['to_section_id'] ?? $employee->section_id;
        $toUnitId = $data['to_unit_id'] ?? $employee->unit_id;

        if (
            $toCompanyId == $employee->company_id &&
            $toBranchId == $employee->branch_id &&
            $toClusterId == $employee->cluster_id &&
            $toDivisionId == $employee->division_id &&
            $toDepartmentId == $employee->department_id &&
            $toSectionId == $employee->section_id &&
            $toUnitId == $employee->unit_id
        ) {
            throw ValidationException::withMessages([
                'to_department_id' => 'The transfer destination matches the current organization structure.',
            ]);
        }
    }

    protected function closeCurrentTransfer(User $employee, string|Carbon $effectiveFrom): void
    {
        $effectiveFrom = Carbon::parse($effectiveFrom)->startOfDay();

        $current = EmployeeTransfer::where('user_id', $employee->id)
            ->where('approval_status', EmployeeTransfer::STATUS_APPROVED)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->startOfDay());
            })
            ->where('effective_from', '<', $effectiveFrom)
            ->orderByDesc('effective_from')
            ->first();

        if ($current) {
            $previousDay = $effectiveFrom->copy()->subDay();
            $current->update([
                'effective_to' => $previousDay,
                'updated_by' => auth()->id() ?? null,
            ]);
        }
    }

    protected function adjustPreviousTransfer(EmployeeTransfer $transfer, Carbon $newStart): void
    {
        $previous = EmployeeTransfer::query()
            ->where('user_id', $transfer->user_id)
            ->where('id', '<>', $transfer->id)
            ->where('effective_from', '<', $newStart)
            ->orderByDesc('effective_from')
            ->first();

        if (!$previous) {
            return;
        }

        $previous->update([
            'effective_to' => $newStart->copy()->subDay(),
            'updated_by' => auth()->id(),
        ]);
    }

    protected function calculateEffectiveTo(
        EmployeeTransfer $transfer,
        Carbon $newStart
    ): ?Carbon {
        $next = EmployeeTransfer::query()
            ->where('user_id', $transfer->user_id)
            ->where('id', '<>', $transfer->id)
            ->where('effective_from', '>', $newStart)
            ->orderBy('effective_from')
            ->first();

        if (!$next) {
            return null;
        }

        return Carbon::parse($next->effective_from)->subDay();
    }

    protected function validateEditable(EmployeeTransfer $transfer): void
    {
        if (
            $transfer->approval_status === EmployeeTransfer::STATUS_APPROVED &&
            $transfer->effective_to &&
            Carbon::parse($transfer->effective_to)->lt(today())
        ) {
            throw ValidationException::withMessages([
                'effective_from' => 'Historical transfer records cannot be edited.',
            ]);
        }
    }

    public function history(User $employee)
    {
        return EmployeeTransfer::query()
            ->with(['fromCompany', 'toCompany', 'fromDepartment', 'toDepartment', 'approver'])
            ->where('user_id', $employee->id)
            ->orderByDesc('effective_from')
            ->get();
    }
}
