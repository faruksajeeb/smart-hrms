<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\HR\Leave\LeaveCalendarService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRLeaveCalendarController extends Controller
{
    public function __construct(
        protected LeaveCalendarService $calendarService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = \Carbon\Carbon::now();

        $year = $request->filled('year') ? (int) $request->integer('year') : $today->year;
        $month = $request->filled('month') ? (int) $request->integer('month') : $today->month;

        $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $filters = [
            'company_id' => $request->integer('company_id'),
            'branch_id' => $request->integer('branch_id'),
            'division_id' => $request->integer('division_id'),
            'department_id' => $request->integer('department_id'),
            'section_id' => $request->integer('section_id'),
            'unit_id' => $request->integer('unit_id'),
            'designation_id' => $request->integer('designation_id'),
            'employment_type_id' => $request->integer('employment_type_id'),
            'leave_type_id' => $request->integer('leave_type_id'),
            'status' => $request->string('status')->toString() ?: 'approved',
            'employee_id' => $request->integer('employee_id'),
            'include_non_leave' => $request->boolean('include_non_leave'),
        ];

        $calendarData = $this->calendarService->getHRCalendar($user, $from, $to, $filters);

        return Inertia::render('HR/Leave/Calendar/Index', [
            'events' => $calendarData['events'],
            'summary' => $calendarData['summary'],
            'filters' => $filters,
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
            'year' => $year,
            'month' => $month,
            'options' => [
                'companies' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_COMPANY)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'branches' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_BRANCH)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'clusters' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_CLUSTER)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'divisions' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_DIVISION)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'departments' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_DEPARTMENT)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'sections' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_SECTION)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'units' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_UNIT)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'designations' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_DESIGNATION)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'employmentTypes' => \App\Models\MasterDataItem::query()
                    ->where('category', \App\Models\MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
                    ->where('status', \App\Models\MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name', 'code']),
                'leaveTypes' => \App\Models\LeaveType::query()
                    ->where('status', 'active')
                    ->orderBy('display_order')
                    ->orderBy('leave_name')
                    ->get(['id', 'leave_name', 'leave_code', 'display_color']),
                'statuses' => [
                    ['value' => 'all', 'label' => 'All'],
                    ['value' => 'approved', 'label' => 'Approved'],
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'rejected', 'label' => 'Rejected'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ['value' => 'withdrawn', 'label' => 'Withdrawn'],
                ],
            ],
        ]);
    }

    public function dayDetails(Request $request)
    {
        $user = $request->user();
        $today = \Carbon\Carbon::now();

        $year = $request->filled('year') ? (int) $request->integer('year') : $today->year;
        $month = $request->filled('month') ? (int) $request->integer('month') : $today->month;
        $day = $request->filled('day') ? (int) $request->integer('day') : $today->day;

        $date = \Carbon\Carbon::create($year, $month, $day);

        $filters = [
            'company_id' => $request->integer('company_id'),
            'branch_id' => $request->integer('branch_id'),
            'division_id' => $request->integer('division_id'),
            'department_id' => $request->integer('department_id'),
            'section_id' => $request->integer('section_id'),
            'unit_id' => $request->integer('unit_id'),
            'designation_id' => $request->integer('designation_id'),
            'employment_type_id' => $request->integer('employment_type_id'),
            'leave_type_id' => $request->integer('leave_type_id'),
            'status' => $request->string('status')->toString() ?: 'approved',
            'employee_id' => $request->integer('employee_id'),
            'include_non_leave' => $request->boolean('include_non_leave'),
        ];

        $dayDetails = $this->calendarService->getHRCalendarDayDetails($user, $date, $filters);

        return response()->json($dayDetails);
    }
}
