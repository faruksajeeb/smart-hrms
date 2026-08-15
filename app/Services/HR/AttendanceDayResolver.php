<?php
namespace App\Services\HR;
use App\Models\User; use Carbon\Carbon;
class AttendanceDayResolver { public function __construct(protected AttendanceScheduleResolver $scheduleResolver) {} public function resolve(User $employee, Carbon $date): array { $data=$this->scheduleResolver->resolve($employee,$date); $data['classification']=$data['approved_leave'] ? (($data['holiday']?'HOLIDAY_AND_':'').($data['weekly_off']?'WEEKLY_OFF_AND_':'').'LEAVE') : ($data['holiday']?'HOLIDAY':($data['weekly_off']?'WEEKLY_OFF':'WORKING_DAY')); return $data; } }
