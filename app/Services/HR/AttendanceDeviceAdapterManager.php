<?php
namespace App\Services\HR;
use App\Contracts\AttendanceDeviceAdapterInterface;
use App\Models\AttendanceDevice;
use Carbon\Carbon;
class AttendanceDeviceAdapterManager { public function adapter(AttendanceDevice $device): AttendanceDeviceAdapterInterface { return app(\App\Services\HR\Adapters\GenericAttendanceApiAdapter::class); } }
