<?php

namespace App\Console\Commands;

use App\Services\HR\Leave\LeaveAccrualService;
use Illuminate\Console\Command;

class LeaveAccrue extends Command
{
    protected $signature = 'leave:accrue
                            {period? : The period to process (Y-m format, e.g., 2026-11)}
                            {--policy= : Process specific leave policy ID}
                            {--dry-run : Show what would be processed without creating entries}
                            {--batch-size=100 : Number of policy assignments to process per batch}';

    protected $description = 'Process leave accrual for eligible employees';

    public function handle(LeaveAccrualService $accrualService): int
    {
        $periodInput = $this->argument('period');
        
        if ($periodInput) {
            $period = \Carbon\Carbon::createFromFormat('Y-m', $periodInput);
        } else {
            $period = \Carbon\Carbon::now()->startOfMonth();
        }

        $options = [
            'batch_size' => (int) $this->option('batch-size'),
        ];

        if ($this->option('policy')) {
            $options['leave_policy_id'] = (int) $this->option('policy');
        }

        $this->info("Processing leave accrual for: {$period->format('Y-m')}");
        
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No entries will be created');
        }

        $startTime = microtime(true);
        
        try {
            $results = $accrualService->processPeriod($period, $options);
        } catch (\Throwable $e) {
            $this->error('Accrual processing failed: ' . $e->getMessage());
            return 1;
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $results['processed']],
                ['Skipped', $results['skipped']],
                ['Failed', $results['failed']],
                ['Duration', $duration . 's'],
            ]
        );

        if (!empty($results['errors'])) {
            $this->warn('Errors encountered:');
            foreach (array_slice($results['errors'], 0, 10) as $error) {
                $this->line("- Assignment {$error['assignment_id']}: {$error['message']}");
            }
            
            if (count($results['errors']) > 10) {
                $this->warn('... and ' . (count($results['errors']) - 10) . ' more errors');
            }
        }

        if ($results['failed'] > 0) {
            return 1;
        }

        $this->info('Accrual processing completed successfully.');
        return 0;
    }
}