<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Services\HR\AttendanceCalendarService;
use App\Services\HR\AttendanceDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceDashboardController extends Controller
{
    public function __construct(protected AttendanceDashboardService $dashboard, protected AttendanceCalendarService $calendar) {}

    public function index(Request $request)
    { return Inertia::render('Attendance/Dashboard', array_merge($this->dashboard->dashboard($request->validate($this->rules()), $request->user()), ['role'=>'hr'])); }

    public function calendar(Request $request)
    { return Inertia::render('Attendance/Calendar', array_merge($this->calendar->month($request->validate(['year'=>'nullable|integer|min:2000|max:2200','month'=>'nullable|integer|min:1|max:12','employee'=>'nullable|integer']), $request->user()), ['role'=>'hr'])); }

    public function ownDashboard(Request $request)
    { return Inertia::render('Attendance/Dashboard', array_merge($this->dashboard->dashboard(['from'=>$request->input('from'),'to'=>$request->input('to'),'employee'=>$request->user()->id], $request->user()), ['role'=>'employee'])); }

    public function ownCalendar(Request $request)
    { return Inertia::render('Attendance/Calendar', array_merge($this->calendar->month(['year'=>$request->input('year'),'month'=>$request->input('month'),'employee'=>$request->user()->id], $request->user()), ['role'=>'employee'])); }

    protected function rules(): array { return ['from'=>'nullable|date','to'=>'nullable|date','company_id'=>'nullable|integer','branch_id'=>'nullable|integer','division_id'=>'nullable|integer','department_id'=>'nullable|integer','section_id'=>'nullable|integer','unit_id'=>'nullable|integer','designation_id'=>'nullable|integer','employment_type_id'=>'nullable|integer','employee'=>'nullable|integer','employee_id'=>'nullable|string|max:50','shift_id'=>'nullable|integer','status'=>'nullable|string|max:40']; }
}
