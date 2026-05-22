<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\DashboardService;

class DashboardController extends Controller
{
     public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Display the admin dashboard.
     */
    
    public function admin(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'summary' => $this->dashboardService->getAdminSummary(),

            // 'quickActions' => [
            //     'Review role assignments',
            //     'Refine permission access',
            //     'Create or update workforce accounts',
            // ],
        ]);
    }

    /**
     * Display the HR dashboard.
     */
    public function hr(Request $request): Response
    {
        return Inertia::render('HR/Dashboard', [
            'summary' => [
                'employeesTracked' => 128,
                'leaveApprovals' => 6,
                'payrollRuns' => 'May cycle',
            ],
            'quickActions' => [
                'Process payroll',
                'Review leave requests',
                'Update employee records',
            ],
        ]);
    }

    /**
     * Display the employee dashboard.
     */
    public function employee(Request $request): Response
    {
        return Inertia::render('Employee/Dashboard', [
            'summary' => [
                'attendanceStatus' => 'Checked in',
                'leaveBalance' => '14 days',
                'nextPayroll' => 'May 31',
            ],
            'quickActions' => [
                'Track attendance',
                'Request leave',
                'Review profile details',
            ],
        ]);
    }
}
