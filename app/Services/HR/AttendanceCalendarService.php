<?php

namespace App\Services\HR;

use App\Models\AttendanceDailyRecord;
use App\Models\User;
use Carbon\Carbon;

class AttendanceCalendarService
{
    public function __construct(protected AttendanceDashboardService $dashboard) {}
    public function month(array $filters, User $viewer): array
    {
        $month = Carbon::createFromDate((int)($filters['year'] ?? now()->year), (int)($filters['month'] ?? now()->month), 1);
        $employee = !empty($filters['employee']) ? (int)$filters['employee'] : $viewer->id;
        if ($employee !== (int)$viewer->id && ! $viewer->can('attendance.calendar.view_all')) {
            abort_unless($viewer->can('attendance.calendar.view_team') && User::whereKey($employee)->where(function ($q) use ($viewer) { $q->where('reporting_manager_id', $viewer->id)->orWhereHas('currentReportingManagerAssignment', fn ($m) => $m->where('manager_id', $viewer->id)); })->exists(), 403);
        }
        $query = AttendanceDailyRecord::with(['user.company','user.branch','user.department','shift','schedule','leaveApplication.leaveType','holiday'])->where('user_id',$employee)->whereBetween('attendance_date',[$month->copy()->startOfMonth()->toDateString(),$month->copy()->endOfMonth()->toDateString()]);
        $records = $query->get()->keyBy(fn($r)=>$r->attendance_date->toDateString());
        $days=[]; for($d=$month->copy()->startOfMonth();$d->lte($month->copy()->endOfMonth());$d->addDay()){ $record=$records->get($d->toDateString()); $days[]=$record ? $this->record($record) : ['date'=>$d->toDateString(),'status'=>'not_processed','processed'=>false]; }
        $summary=$this->summary($days); return ['period'=>['year'=>$month->year,'month'=>$month->month,'label'=>$month->format('F Y')],'employee'=>User::with(['company','branch','department'])->findOrFail($employee),'summary'=>$summary,'days'=>$days,'options'=>$this->dashboard->dashboard(['from'=>$month->startOfMonth()->toDateString(),'to'=>$month->endOfMonth()->toDateString(),'employee'=>$employee],$viewer)['options']];
    }
    protected function record(AttendanceDailyRecord $r): array { return ['id'=>$r->id,'date'=>$r->attendance_date->toDateString(),'status'=>$r->attendance_status,'processed'=>true,'first_in'=>$r->first_in?->format('H:i'),'last_out'=>$r->last_out?->format('H:i'),'worked_minutes'=>(int)$r->worked_minutes,'scheduled_minutes'=>(int)$r->scheduled_minutes,'late_minutes'=>(int)$r->late_minutes,'early_out_minutes'=>(int)$r->early_out_minutes,'overtime_minutes'=>(int)$r->calculated_overtime_minutes,'employee'=>$r->user?->only(['id','name','employee_id']),'shift'=>$r->shift?->shift_name,'leave'=>$r->leaveApplication?->leaveType?->name,'holiday'=>$r->holiday?->holiday_name,'remarks'=>$r->remarks,'lifecycle_status'=>$r->lifecycle_status]; }
    protected function summary(array $days): array { $processed=array_filter($days,fn($d)=>$d['processed']); return ['working_days'=>count(array_filter($processed,fn($d)=>!in_array($d['status'],['weekly_off','holiday','not_applicable']))),'present_days'=>count(array_filter($processed,fn($d)=>in_array($d['status'],['present','late','early_out','late_early_out','half_day']))),'leave_days'=>count(array_filter($processed,fn($d)=>$d['status']==='leave')),'absent_days'=>count(array_filter($processed,fn($d)=>$d['status']==='absent')),'late_days'=>count(array_filter($processed,fn($d)=>in_array($d['status'],['late','late_early_out']))),'early_out_days'=>count(array_filter($processed,fn($d)=>in_array($d['status'],['early_out','late_early_out']))),'missing_punch_days'=>count(array_filter($processed,fn($d)=>$d['status']==='missing_punch')),'overtime_minutes'=>array_sum(array_column($processed,'overtime_minutes')),'unprocessed_days'=>count($days)-count($processed)]; }
}
