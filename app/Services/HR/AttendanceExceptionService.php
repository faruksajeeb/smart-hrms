<?php
namespace App\Services\HR;
use App\Models\AttendanceDailyRecord;
use App\Models\AttendanceException;
class AttendanceExceptionService { public function scan(string $from,string $to):int{$count=0;AttendanceDailyRecord::whereBetween('attendance_date',[$from,$to])->whereIn('attendance_status',['missing_punch','absent'])->chunkById(200,function($rows)use(&$count){foreach($rows as $r){AttendanceException::firstOrCreate(['attendance_daily_record_id'=>$r->id,'exception_type'=>$r->attendance_status,'exception_date'=>$r->attendance_date],['user_id'=>$r->user_id,'severity'=>$r->attendance_status==='absent'?'high':'medium','status'=>'open','source'=>'processing','details'=>$r->remarks]);$count++;}});return $count;} public function resolve(AttendanceException $exception,int $actorId):void{$exception->update(['status'=>'resolved','resolved_by'=>$actorId,'resolved_at'=>now()]);} }
