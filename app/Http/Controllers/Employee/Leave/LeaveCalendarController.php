<?php

namespace App\Http\Controllers\Employee\Leave;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HR\Leave\LeaveCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveCalendarController extends Controller
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
            'include_non_leave' => $request->boolean('include_non_leave'),
        ];

        $events = $this->calendarService->getEmployeeCalendar($user, $from, $to, $filters);
// dd($events);
        return Inertia::render('Employee/Leave/Calendar/Index', [
            'events' => $events,
            'filters' => $filters,
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'currentMonth' => $today->format('Y-m'),
        ]);
    }
}