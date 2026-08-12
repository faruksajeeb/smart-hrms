<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HR\Leave\LeaveCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamLeaveCalendarController extends Controller
{
    public function __construct(
        protected LeaveCalendarService $calendarService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = \Carbon\Carbon::now();
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->string('from')) : $today->copy()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->string('to')) : $today->copy()->endOfMonth();

        $filters = [
            'leave_type_id' => $request->integer('leave_type_id'),
            'status' => $request->string('status'),
            'department_id' => $request->integer('department_id'),
            'section_id' => $request->integer('section_id'),
            'unit_id' => $request->integer('unit_id'),
            'employee_id' => $request->integer('employee_id'),
            'include_non_leave' => $request->boolean('include_non_leave'),
        ];

        // Determine scope based on role
        $isManager = $user->hasRole(User::ROLE_EMPLOYEE) && User::where('reporting_manager_id', $user->id)->exists();
        
        if ($isManager) {
            $events = $this->calendarService->getTeamCalendar($user, $from, $to, $filters);
        } else {
            // HR/Admin scope - get all employees under their organizational scope
            $events = $this->calendarService->getTeamCalendar($user, $from, $to, $filters);
        }

        return Inertia::render('HR/Leave/TeamCalendar/Index', [
            'events' => $events,
            'filters' => $filters,
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'currentMonth' => $today->format('Y-m'),
            'isManager' => $isManager,
        ]);
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $date = $request->filled('date') ? \Carbon\Carbon::parse($request->string('date')) : \Carbon\Carbon::now();

        $isManager = $user->hasRole(User::ROLE_EMPLOYEE) && User::where('reporting_manager_id', $user->id)->exists();

        if ($isManager) {
            $summary = $this->calendarService->getTeamLeaveSummary($user, $date);
        } else {
            $summary = $this->calendarService->getTeamLeaveSummary($user, $date);
        }

        return Inertia::render('HR/Leave/TeamCalendar/Summary', [
            'summary' => $summary,
            'date' => $date->format('Y-m-d'),
        ]);
    }
}