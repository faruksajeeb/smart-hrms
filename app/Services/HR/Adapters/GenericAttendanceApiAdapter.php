<?php
namespace App\Services\HR\Adapters;
use App\Contracts\AttendanceDeviceAdapterInterface;
use App\Models\AttendanceDevice;
use Carbon\Carbon;
class GenericAttendanceApiAdapter implements AttendanceDeviceAdapterInterface { public function testConnection(AttendanceDevice $device): bool { return $device->status === 'active'; } public function fetchLogs(AttendanceDevice $device, Carbon $from, Carbon $to): iterable { return []; } }
