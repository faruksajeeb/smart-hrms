<?php
namespace App\Contracts;
interface AttendanceDeviceConnectorInterface { public function sync(array $options=[]): array; }
