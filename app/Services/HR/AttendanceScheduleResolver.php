<?php
namespace App\Services\HR;
use App\Models\EmployeeShiftAssignment; use App\Models\HolidayCalendar; use App\Models\EmployeeWeeklyOffAssignment; use App\Models\Shift; use App\Models\ShiftSchedule; use App\Models\User; use App\Models\LeaveApplicationDay; use Carbon\Carbon;

class AttendanceScheduleResolver
{
    public function __construct(protected AttendancePolicyResolver $policyResolver) {}
    public function resolve(User $employee, Carbon $date): array
    {
        $date = $date->copy()->startOfDay(); $dateString = $date->toDateString();
        $schedule = ShiftSchedule::with('shift')->where('user_id',$employee->id)->whereDate('work_date',$dateString)->whereNotIn('status',[ShiftSchedule::STATUS_CANCELLED])->first();
        $assignment = EmployeeShiftAssignment::with('shift')->where('user_id',$employee->id)->whereDate('effective_from','<=',$dateString)->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$dateString))->latest('effective_from')->first();
        $shift = $schedule?->shift ?? $assignment?->shift ?? Shift::active()->where('company_id',$employee->company_id)->orderBy('id')->first();
        $startTime = $schedule?->start_time ?? $shift?->start_time; $endTime = $schedule?->end_time ?? $shift?->end_time;
        $crossMidnight = $shift?->is_night_shift || ($startTime && $endTime && $endTime <= $startTime);
        $weekly = EmployeeWeeklyOffAssignment::with('weeklyOffPolicy.days')->where('user_id',$employee->id)->whereDate('effective_from','<=',$dateString)->where(fn($q)=>$q->whereNull('effective_to')->orWhereDate('effective_to','>=',$dateString))->latest('effective_from')->first();
        $weeklyOff = (bool) $weekly?->weeklyOffPolicy?->days?->first(fn($d) => (int)$d->day_of_week === $date->dayOfWeek && (!$d->effective_from || $d->effective_from <= $dateString) && (!$d->effective_to || $d->effective_to >= $dateString) && (bool)$d->status);
        $holiday = HolidayCalendar::with('scopes')->whereDate('holiday_date',$dateString)->where('status','active')->get()->first(fn($h) => $h->scopes->isEmpty() || $h->scopes->contains(fn($s)=> (!$s->company_id || (int)$s->company_id === (int)$employee->company_id) && (!$s->branch_id || (int)$s->branch_id === (int)$employee->branch_id) && (!$s->division_id || (int)$s->division_id === (int)$employee->division_id) && (!$s->department_id || (int)$s->department_id === (int)$employee->department_id)));
        $leave = LeaveApplicationDay::with(['application.leaveType'])->whereDate('leave_date',$dateString)->where('counts_as_leave',true)->whereHas('application',fn($q)=>$q->where('user_id',$employee->id)->where('status','approved'))->first();
        $policyAssignment = $this->policyResolver->resolveForEmployee($employee,$date);
        $classification = $leave
            ? (($holiday ? 'HOLIDAY_AND_' : '').($weeklyOff ? 'WEEKLY_OFF_AND_' : '').'LEAVE')
            : ($holiday ? 'HOLIDAY' : ($weeklyOff ? 'WEEKLY_OFF' : 'WORKING_DAY'));

        return ['date'=>$dateString,'shift'=>$shift,'shift_assignment'=>$assignment,'roster'=>$schedule,'shift_start'=>$startTime,'shift_end'=>$endTime,'cross_midnight'=>(bool)$crossMidnight,'weekly_off'=>$weeklyOff,'weekly_off_assignment'=>$weekly,'holiday'=>$holiday,'approved_leave'=>$leave,'attendance_policy'=>$policyAssignment?->policy,'attendance_policy_assignment'=>$policyAssignment,'expected_working_day'=>!$weeklyOff && !$holiday && !$leave,'classification'=>$classification];
    }
}
