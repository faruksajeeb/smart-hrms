<?php

use App\Http\Controllers\HR\AttendanceDeviceIntegrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->post('/attendance/devices/{deviceCode}/webhook', [AttendanceDeviceIntegrationController::class, 'webhook'])->name('attendance.devices.webhook');
