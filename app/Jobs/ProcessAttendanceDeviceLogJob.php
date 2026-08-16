<?php
namespace App\Jobs;
use App\Models\AttendanceDeviceLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
class ProcessAttendanceDeviceLogJob implements ShouldQueue { use Queueable; public $tries=3; public $timeout=120; public function __construct(public int $logId,public ?int $actorId=null){} public function handle(): void { $log=AttendanceDeviceLog::with('device')->find($this->logId); if(!$log||in_array($log->processing_status,['processed','duplicate'],true))return; try{app(\App\Services\HR\AttendanceDeviceLogService::class)->process($log,$this->actorId);}catch(\Throwable $e){$log->increment('retry_count');$log->update(['processing_status'=>'failed','error_message'=>'Device log processing failed.']);throw $e;} } }
