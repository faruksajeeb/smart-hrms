<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HR\Leave\LeaveDashboardService;
use App\Services\HR\Leave\LeaveCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRLeaveDashboardController extends Controller
{
    public function __construct(
        protected LeaveDashboardService $dashboardService,
        protected LeaveCalendarService $calendarService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $month = $request->filled('month') ? \Carbon\Carbon::parse($request->string('month')) : null;

        $kpis = $this->dashboardService->getHRDashboard($user, $month);
        $leaveTypeDistribution = $this->dashboardService->getLeaveTypeDistribution($user, $month);
        $monthlyTrend = $this->dashboardService->getMonthlyTrend($user, 12);
        $departmentAnalysis = $this->dashboardService->getDepartmentAnalysis($user, $month);
        $branchAnalysis = $this->dashboardService->getBranchAnalysis($user, $month);
        $currentlyOnLeave = $this->calendarService->getCurrentlyOnLeave($user, \Carbon\Carbon::now(), \Carbon\Carbon::now()->addDays(7));
        $upcomingLeaves = $this->calendarService->getUpcomingLeaves($user, 30);
      
        $pendingReport = $this->dashboardService->getPendingLeaveReport($user);
//   dd($pendingReport->toArray());
  
        return Inertia::render('HR/Leave/HRDashboard/Index', [
            'kpis' => $kpis,
            'leaveTypeDistribution' => $leaveTypeDistribution,
            'monthlyTrend' => $monthlyTrend,
            'departmentAnalysis' => $departmentAnalysis,
            'branchAnalysis' => $branchAnalysis,
            'currentlyOnLeave' => $currentlyOnLeave,
            'upcomingLeaves' => $upcomingLeaves,
            'pendingReport' => $pendingReport,
            'month' => $month?->format('Y-m') ?? \Carbon\Carbon::now()->format('Y-m'),
        ]);
    }
}