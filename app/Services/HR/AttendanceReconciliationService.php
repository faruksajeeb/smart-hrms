<?php
namespace App\Services\HR;
use App\Models\AttendanceDailyRecord;
use App\Models\AttendancePunch;
use App\Models\AttendanceReconciliationRecord;
use Illuminate\Support\Facades\DB;
class AttendanceReconciliationService { public function scan(string $from,string $to): int {$count=0;AttendancePunch::query()->whereBetween('punch_date',[$from,$to])->select('user_id','punch_date')->groupBy('user_id','punch_date')->chunk(200,function($rows)use(&$count){foreach($rows as $row){if(!AttendanceDailyRecord::where('user_id',$row->user_id)->whereDate('attendance_date',$row->punch_date)->exists()){AttendanceReconciliationRecord::firstOrCreate(['user_id'=>$row->user_id,'attendance_date'=>$row->punch_date,'issue_type'=>'raw_punch_without_attendance'],['status'=>'open','severity'=>'high','details'=>'Raw punch exists without processed attendance.']);$count++;}}});return $count;} public function resolve(AttendanceReconciliationRecord $record,int $actorId):void{$record->update(['status'=>'resolved','resolved_by'=>$actorId,'resolved_at'=>now()]);} }
