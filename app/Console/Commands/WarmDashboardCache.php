<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\DashboardService;

#[Signature('cache:warm-dashboard')]
#[Description('Command description')]
class WarmDashboardCache extends Command
{
    protected $signature = 'cache:warm-dashboard';

    protected $description = 'Warm dashboard cache';

    public function handle(
        DashboardService $dashboardService
    ): void {

        $dashboardService->getAdminSummary();

        $this->info('Dashboard cache warmed.');
    }
}
