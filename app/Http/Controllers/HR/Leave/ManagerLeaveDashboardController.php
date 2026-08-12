<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HR\Leave\LeaveDashboardService;
use App\Services\HR\Leave\LeaveCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ManagerLeaveDashboardController extends Controller
{
    public function __construct(
        protected LeaveDashboardService $dashboardService,
        protected LeaveCalendarService $calendarService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $month = $request->filled('month') ? \Carbon\Carbon::parse($request->string('month')) : null;

        $kpis = $this->dashboardService->getManagerDashboard($user, $month);
        $pendingApprovals = $this->dashboardService->getManagerPendingApprovals($user);
        $teamOnLeaveToday = $this->dashboardService->getManagerTeamOnLeaveToday($user);
        $upcomingLeaves = $this->calendarService->getUpcomingLeaves($user, 30);

        return Inertia::render('HR/Leave/ManagerDashboard/Index', [
            'kpis' => $kpis,
            'pendingApprovals' => $pendingApprovals,
            'teamOnLeaveToday' => $teamOnLeaveToday,
            'upcomingLeaves' => $upcomingLeaves,
            'month' => $month?->format('Y-m') ?? \Carbon\Carbon::now()->format('Y-m'),
        ]);
    }
}