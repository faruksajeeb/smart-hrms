<?php
namespace App\Contracts;
use App\Models\AttendanceDevice;
use Carbon\Carbon;
interface AttendanceDeviceAdapterInterface { public function testConnection(AttendanceDevice $device): bool; public function fetchLogs(AttendanceDevice $device, Carbon $from, Carbon $to): iterable; }
