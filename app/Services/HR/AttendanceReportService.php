<?php

namespace App\Services\HR;

use App\Models\AttendanceDailyRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    public const TYPES = ['daily','monthly','employee','department','late-early','absenteeism','overtime','summary'];

    public function report(string $type, array $filters, User $viewer): array
    {
        return match ($type) {
            'monthly' => $this->monthly($filters, $viewer),
            'employee' => $this->employee($filters, $viewer),
            'department' => $this->department($filters, $viewer),
            'late-early' => $this->lateEarly($filters, $viewer),
            'absenteeism' => $this->absenteeism($filters, $viewer),
            'overtime' => $this->overtime($filters, $viewer),
            'summary' => $this->summary($filters, $viewer),
            default => $this->daily($filters, $viewer),
        };
    }

    public function export(string $type, array $filters, User $viewer): \Generator
    {
        $query = $this->base($filters, $viewer)->with(['user.department','user.company','user.branch','shift']);
        foreach ($query->lazyById(500, 'attendance_daily_records.id') as $record) yield $this->dailyRow($record);
    }

    protected function daily(array $filters, User $viewer): array
    { $rows=$this->base($filters,$viewer)->with(['user.company','user.branch','user.division','user.department','user.section','user.unit','user.designation','shift'])->paginate($this->perPage($filters))->withQueryString(); return ['kind'=>'daily','columns'=>['Employee ID','Employee Name','Company','Branch','Division','Department','Section','Unit','Designation','Shift','Attendance Date','First In','Last Out','Worked Hours','Late Minutes','Early Out Minutes','Overtime Hours','Attendance Status','Regularization Status','Remarks'],'rows'=>$rows,'summary'=>$this->counts($filters,$viewer),'title'=>'Daily Attendance Report']; }

    protected function monthly(array $filters, User $viewer): array
    { $query=$this->base($filters,$viewer)->join('users','users.id','=','attendance_daily_records.user_id')->selectRaw("users.id as employee_id, users.employee_id as employee_code, users.name, COUNT(*) as total_days, SUM(CASE WHEN attendance_status IN ('present','late','early_out','late_early_out') THEN 1 ELSE 0 END) as present_days, SUM(attendance_status='absent') as absent_days, SUM(attendance_status='leave') as leave_days, SUM(attendance_status='holiday') as holiday_days, SUM(attendance_status='weekly_off') as weekly_off_days, SUM(attendance_status='half_day') as half_days, SUM(attendance_status IN ('late','late_early_out')) as late_days, SUM(attendance_status IN ('early_out','late_early_out')) as early_out_days, SUM(attendance_status='missing_punch') as missing_punch_days, SUM(worked_minutes) as worked_minutes, SUM(calculated_overtime_minutes) as overtime_minutes")->groupBy('users.id','users.employee_id','users.name')->orderBy('users.name'); return ['kind'=>'monthly','columns'=>['Employee ID','Employee Name','Total Days','Present','Absent','Leave','Holiday','Weekly Off','Half Days','Late','Early Out','Missing Punch','Worked Hours','Overtime Hours'],'rows'=>$query->paginate($this->perPage($filters))->withQueryString(),'summary'=>$this->counts($filters,$viewer),'title'=>'Monthly Attendance Report']; }

    protected function employee(array $filters, User $viewer): array
    { $filters['employee']=$filters['employee']??$viewer->id; $rows=$this->base($filters,$viewer)->with(['user.company','user.branch','user.department','user.designation','shift'])->paginate($this->perPage($filters))->withQueryString(); return ['kind'=>'employee','columns'=>['Date','Employee','Shift','First In','Last Out','Worked Hours','Late Minutes','Early Out Minutes','Overtime Hours','Status','Remarks'],'rows'=>$rows,'summary'=>$this->counts($filters,$viewer),'title'=>'Employee Attendance Report']; }

    protected function department(array $filters, User $viewer): array
    { $query=$this->base($filters,$viewer)->join('users','users.id','=','attendance_daily_records.user_id')->leftJoin('master_data_items as d','d.id','=','users.department_id')->selectRaw("COALESCE(d.name,'Unassigned') as department, COUNT(DISTINCT users.id) as employees, SUM(attendance_status IN ('present','late','early_out','late_early_out')) as present_days, SUM(attendance_status='absent') as absent_days, SUM(attendance_status='leave') as leave_days, SUM(attendance_status IN ('late','late_early_out')) as late_days, SUM(attendance_status IN ('early_out','late_early_out')) as early_out_days, SUM(attendance_status='missing_punch') as missing_punch_days, SUM(calculated_overtime_minutes) as overtime_minutes, SUM(attendance_status NOT IN ('weekly_off','holiday','not_applicable')) as working_days")->groupBy('d.id','d.name')->orderBy('d.name'); return ['kind'=>'department','columns'=>['Department','Employees','Present Days','Absent Days','Leave Days','Late Days','Early Out Days','Missing Punch','Overtime Hours','Attendance %'],'rows'=>$query->paginate($this->perPage($filters))->withQueryString(),'summary'=>$this->counts($filters,$viewer),'title'=>'Department-wise Attendance Report']; }

    protected function lateEarly(array $filters, User $viewer): array
    { $query=$this->base($filters,$viewer)->where(fn($q)=>$q->where('late_minutes','>',0)->orWhere('early_out_minutes','>',0))->with(['user.department','shift'])->orderByDesc('late_minutes'); return ['kind'=>'late-early','columns'=>['Employee','Department','Date','Shift','Actual In','Late Minutes','Actual Out','Early Out Minutes','Regularization Status'],'rows'=>$query->paginate($this->perPage($filters))->withQueryString(),'summary'=>$this->counts($filters,$viewer),'title'=>'Late & Early Out Report']; }

    protected function absenteeism(array $filters, User $viewer): array
    { $query=$this->base($filters,$viewer)->where('attendance_status','absent')->with(['user.department'])->orderBy('attendance_date'); return ['kind'=>'absenteeism','columns'=>['Employee','Department','Date','Status','Remarks'],'rows'=>$query->paginate($this->perPage($filters))->withQueryString(),'summary'=>$this->counts($filters,$viewer),'title'=>'Absenteeism Report']; }

    protected function overtime(array $filters, User $viewer): array
    { $query=$this->base($filters,$viewer)->where('calculated_overtime_minutes','>',0)->with(['user.department','shift'])->orderByDesc('calculated_overtime_minutes'); return ['kind'=>'overtime','columns'=>['Employee','Department','Date','Shift','Scheduled Hours','Worked Hours','Overtime Hours','Status','Remarks'],'rows'=>$query->paginate($this->perPage($filters))->withQueryString(),'summary'=>$this->counts($filters,$viewer),'title'=>'Overtime Report']; }

    protected function summary(array $filters, User $viewer): array
    { return ['kind'=>'summary','columns'=>['Status','Records'],'rows'=>collect($this->counts($filters,$viewer))->map(fn($v,$k)=>(object)['status'=>$k,'records'=>$v])->values(),'summary'=>$this->counts($filters,$viewer),'title'=>'Attendance Summary']; }

    protected function base(array $filters, User $viewer): Builder
    { $q=AttendanceDailyRecord::query(); if(! $viewer->can('attendance.calendar.view_all') && ! $viewer->can('attendance.reports.view')) $q->where('user_id',$viewer->id); foreach(['company_id','branch_id','division_id','department_id','section_id','unit_id','designation_id','employment_type_id'] as $field) if(!empty($filters[$field])) $q->whereHas('user',fn($u)=>$u->where($field,$filters[$field])); if(!empty($filters['employee']))$q->where('user_id',$filters['employee']); if(!empty($filters['employee_id']))$q->whereHas('user',fn($u)=>$u->where('employee_id','like','%'.$filters['employee_id'].'%')); if(!empty($filters['shift_id']))$q->where('shift_id',$filters['shift_id']); if(!empty($filters['status']))$q->where('attendance_status',$filters['status']); [$from,$to]=$this->dates($filters); return $q->whereBetween('attendance_date',[$from,$to]); }
    protected function dates(array $filters): array { if(!empty($filters['month'])){ $date=\Carbon\Carbon::createFromDate((int)($filters['year']?:now()->year),(int)$filters['month'],1); return [$date->copy()->startOfMonth()->toDateString(),$date->copy()->endOfMonth()->toDateString()]; } $from=$filters['from']??now()->startOfMonth()->toDateString(); $to=$filters['to']??now()->endOfMonth()->toDateString(); if($from>$to)throw new \InvalidArgumentException('Date From cannot be after Date To.'); return [$from,$to]; }
    protected function counts(array $filters, User $viewer): array { return $this->base($filters,$viewer)->selectRaw('attendance_status, COUNT(*) as total')->groupBy('attendance_status')->pluck('total','attendance_status')->toArray(); }
    protected function perPage(array $filters): int { return min(100,max(10,(int)($filters['per_page']??25))); }
    protected function dailyRow($r): array { return [$r->user?->employee_id,$r->user?->name,$r->user?->company?->name,$r->user?->branch?->name,$r->user?->division?->name,$r->user?->department?->name,$r->user?->section?->name,$r->user?->unit?->name,$r->user?->designation?->name,$r->shift?->shift_name,$r->attendance_date?->toDateString(),$r->first_in?->format('Y-m-d H:i'),$r->last_out?->format('Y-m-d H:i'),round($r->worked_minutes/60,2),$r->late_minutes,$r->early_out_minutes,round($r->calculated_overtime_minutes/60,2),$r->attendance_status,'',$r->remarks]; }
}
