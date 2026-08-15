<?php
namespace App\Console\Commands;
use App\Services\HR\AttendanceProcessingService;
use Illuminate\Console\Command;
class ProcessAttendanceCommand extends Command
{
    protected $signature = 'attendance:process {--date=} {--from=} {--to=} {--employee=} {--company=} {--force}';
    protected $description = 'Process raw attendance punches into daily attendance results';
    public function handle(AttendanceProcessingService $service): int
    {
        $from = $this->option('from') ?: ($this->option('date') ?: now()->toDateString());
        $to = $this->option('to') ?: $from;
        $count = $service->processDateRange($from, $to, $this->getUserId(), $this->option('employee') ? (int)$this->option('employee') : null, $this->option('company') ? (int)$this->option('company') : null, (bool)$this->option('force'));
        $this->info("Processed {$count} attendance record(s).");
        return self::SUCCESS;
    }
    protected function getUserId(): ?int { return auth()->id(); }
}
