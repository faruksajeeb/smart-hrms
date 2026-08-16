<?php
namespace App\Services\HR;
use App\Models\AttendanceAuditLog;
use Illuminate\Http\Request;
class AttendanceAuditService { public function record(string $action, array $data=[], ?Request $request=null): AttendanceAuditLog { $request??=request(); return AttendanceAuditLog::create(['user_id'=>$request->user()?->id,'employee_id'=>$data['employee_id']??null,'attendance_id'=>$data['attendance_id']??null,'action'=>$action,'old_values'=>$data['old_values']??null,'new_values'=>$data['new_values']??null,'reason'=>$data['reason']??null,'source'=>$data['source']??'web','ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'performed_at'=>now()]); } }
