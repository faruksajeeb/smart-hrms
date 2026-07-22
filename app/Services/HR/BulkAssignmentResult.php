<?php

namespace App\Services\HR;

class BulkAssignmentResult
{
    public int $totalSelected = 0;

    public int $successCount = 0;

    public int $skippedCount = 0;

    public int $failedCount = 0;

    /** @var array<int, array{employee_id: int, reason: string}> */
    public array $skipped = [];

    /** @var array<int, array{employee_id: int, reason: string}> */
    public array $failures = [];

    public function incrementSuccess(): void
    {
        $this->successCount++;
    }

    /** @return array{employee_id: int, reason: string} */
    public function addSkip(int $employeeId, string $reason): void
    {
        $this->skipped[] = ['employee_id' => $employeeId, 'reason' => $reason];
        $this->skippedCount++;
    }

    /** @return array{employee_id: int, reason: string} */
    public function addFailure(int $employeeId, string $reason): void
    {
        $this->failures[] = ['employee_id' => $employeeId, 'reason' => $reason];
        $this->failedCount++;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'total_selected' => $this->totalSelected,
            'success_count' => $this->successCount,
            'skipped_count' => $this->skippedCount,
            'failed_count' => $this->failedCount,
            'skipped' => $this->skipped,
            'failures' => $this->failures,
        ];
    }
}
