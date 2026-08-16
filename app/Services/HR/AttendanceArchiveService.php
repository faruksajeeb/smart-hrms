<?php
namespace App\Services\HR;
use App\Models\AttendanceArchive;
use App\Models\AttendanceDailyRecord;
use App\Models\AttendancePayrollPeriod;
use Illuminate\Support\Facades\DB;
class AttendanceArchiveService { public function archive(string $before,int $actorId):int{$count=0;AttendanceDailyRecord::with('user')->whereDate('attendance_date','<',$before)->whereNotExists(fn($q)=>$q->from('attendance_payroll_periods as p')->whereColumn('p.period_from','<=','attendance_daily_records.attendance_date')->whereColumn('p.period_to','>=','attendance_daily_records.attendance_date')->whereIn('p.status',['open','processing','review','reopened']))->chunkById(100,function($rows)use(&$count,$actorId){foreach($rows as $r){AttendanceArchive::firstOrCreate(['attendance_daily_record_id'=>$r->id],['user_id'=>$r->user_id,'attendance_date'=>$r->attendance_date,'record_data'=>$r->toArray(),'organization_snapshot'=>['company_id'=>$r->user?->company_id,'branch_id'=>$r->user?->branch_id,'department_id'=>$r->user?->department_id],'archived_by'=>$actorId,'archived_at'=>now()]);$count++;}});return $count;} }
