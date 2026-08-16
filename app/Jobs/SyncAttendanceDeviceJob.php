<?php
namespace App\Jobs;
use App\Models\AttendanceDevice;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
class SyncAttendanceDeviceJob implements ShouldQueue { use Queueable; public $tries=2; public function __construct(public int $deviceId,public string $from,public string $to,public ?int $actorId=null,public string $type='scheduled'){} public function handle(): void { $device=AttendanceDevice::findOrFail($this->deviceId); app(\App\Services\HR\AttendanceDeviceSyncService::class)->sync($device,Carbon::parse($this->from),Carbon::parse($this->to),$this->actorId,$this->type); } }
