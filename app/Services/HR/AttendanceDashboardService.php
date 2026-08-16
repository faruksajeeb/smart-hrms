<?php

namespace App\Services\HR;

use App\Models\AttendanceDailyRecord;
use App\Models\MasterDataItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceDashboardService
{
    public function dashboard(array $filters, User $viewer): array
    {
        [$from, $to] = $this->period($filters);
        $query = $this->scopedQuery($filters, $viewer)->whereBetween('attendance_date', [$from, $to]);
        $statusCounts = (clone $query)->selectRaw('attendance_status, COUNT(*) as total')->groupBy('attendance_status')->pluck('total', 'attendance_status');
        $trendRows = (clone $query)->selectRaw('attendance_date, attendance_status, COUNT(*) as total')->groupBy('attendance_date', 'attendance_status')->orderBy('attendance_date')->get();
        $departmentRows = (clone $query)->join('users', 'users.id', '=', 'attendance_daily_records.user_id')->leftJoin('master_data_items as departments', 'departments.id', '=', 'users.department_id')->selectRaw("COALESCE(departments.name, 'Unassigned') as department, COUNT(*) as total_days, SUM(CASE WHEN attendance_status IN ('present','late','early_out','late_early_out','half_day') THEN 1 ELSE 0 END) as present_days, SUM(CASE WHEN attendance_status = 'absent' THEN 1 ELSE 0 END) as absent_days, SUM(CASE WHEN attendance_status = 'leave' THEN 1 ELSE 0 END) as leave_days, SUM(CASE WHEN attendance_status IN ('late','late_early_out') THEN 1 ELSE 0 END) as late_days, SUM(CASE WHEN attendance_status NOT IN ('weekly_off','holiday','not_applicable') THEN 1 ELSE 0 END) as working_days")->groupBy('departments.id', 'departments.name')->get()->map(fn ($row) => ['department'=>$row->department,'total_days'=>(int)$row->total_days,'present_days'=>(int)$row->present_days,'absent_days'=>(int)$row->absent_days,'leave_days'=>(int)$row->leave_days,'late_days'=>(int)$row->late_days,'attendance_percentage'=>(int)$row->working_days ? round(((int)$row->present_days / (int)$row->working_days) * 100, 2) : 0])->values();
        $overtime = (clone $query)->selectRaw('COALESCE(SUM(calculated_overtime_minutes),0) as minutes, COUNT(DISTINCT user_id) as employees')->first();
        return ['period'=>['from'=>$from,'to'=>$to], 'definition'=>'Attendance percentage = working-day processed records with present/late/early-out/half-day status divided by all processed working-day records; weekly offs and holidays are excluded.', 'kpis'=>$this->kpis($statusCounts, (int)($overtime->minutes ?? 0)), 'status_distribution'=>$statusCounts->toArray(), 'daily_trend'=>$this->trend($trendRows, $from, $to), 'department_summary'=>$departmentRows, 'overtime_summary'=>['minutes'=>(int)($overtime->minutes ?? 0),'hours'=>round(((int)($overtime->minutes ?? 0))/60,2),'employees'=>(int)($overtime->employees ?? 0)], 'filters'=>$filters, 'options'=>$this->options($viewer)];
    }

    public function records(array $filters, User $viewer)
    {
        return $this->scopedQuery($filters, $viewer)->with(['user.department','user.company','user.branch','shift','schedule'])->orderByDesc('attendance_date')->paginate(25)->withQueryString();
    }

    protected function scopedQuery(array $filters, User $viewer): Builder
    {
        $query = AttendanceDailyRecord::query();
        if (! $viewer->can('attendance.calendar.view_all') && ! $viewer->can('attendance.dashboard.view')) {
            if ($viewer->can('attendance.calendar.view_team') || $viewer->can('attendance.dashboard.view_team')) {
                $query->whereHas('user', fn ($q) => $q->where('reporting_manager_id', $viewer->id)->orWhereHas('currentReportingManagerAssignment', fn ($m) => $m->where('manager_id', $viewer->id)));
            } else {
                $query->where('user_id', $viewer->id);
            }
        }
        foreach (['company_id','branch_id','division_id','department_id','section_id','unit_id','designation_id','employment_type_id'] as $field) if (!empty($filters[$field])) $query->whereHas('user', fn ($q) => $q->where($field, $filters[$field]));
        if (!empty($filters['employee_id'])) $query->whereHas('user', fn ($q) => $q->where('employee_id','like','%'.$filters['employee_id'].'%'));
        if (!empty($filters['employee'])) $query->where('user_id', $filters['employee']);
        if (!empty($filters['shift_id'])) $query->where('shift_id', $filters['shift_id']);
        if (!empty($filters['status'])) $query->where('attendance_status', $filters['status']);
        return $query;
    }

    protected function period(array $filters): array
    { $from = !empty($filters['from']) ? Carbon::parse($filters['from'])->toDateString() : now()->startOfMonth()->toDateString(); $to = !empty($filters['to']) ? Carbon::parse($filters['to'])->toDateString() : now()->endOfMonth()->toDateString(); if ($from > $to) throw new \InvalidArgumentException('Date From cannot be after Date To.'); return [$from,$to]; }
    protected function kpis(Collection $counts, int $overtime): array { return collect(['present','absent','late','early_out','late_early_out','half_day','leave','weekly_off','holiday','missing_punch','not_applicable'])->mapWithKeys(fn($status)=>[$status.'_days'=>(int)$counts->get($status,0)])->put('overtime_minutes',$overtime)->all(); }
    protected function trend(Collection $rows, string $from, string $to): array { $byDate=$rows->groupBy(fn($r)=>Carbon::parse($r->attendance_date)->toDateString()); $out=[]; for($d=Carbon::parse($from);$d->lte(Carbon::parse($to));$d->addDay()){ $items=$byDate->get($d->toDateString(),collect())->keyBy('attendance_status'); $out[]=['date'=>$d->toDateString(),'present'=>(int)($items->get('present')?->total??0),'absent'=>(int)($items->get('absent')?->total??0),'leave'=>(int)($items->get('leave')?->total??0),'late'=>(int)($items->get('late')?->total??0)+(int)($items->get('late_early_out')?->total??0),'missing_punch'=>(int)($items->get('missing_punch')?->total??0)]; } return $out; }
    protected function options(User $viewer): array { $employees=User::query()->where('status',User::STATUS_ACTIVE)->select('id','name','employee_id')->when(! $viewer->can('attendance.calendar.view_all') && ! $viewer->can('attendance.dashboard.view'),fn($q)=>$q->whereKey($viewer->id))->orderBy('name')->limit(100)->get(); return ['employees'=>$employees,'statuses'=>['present','absent','late','early_out','late_early_out','half_day','leave','weekly_off','holiday','missing_punch','not_applicable']]; }
}
