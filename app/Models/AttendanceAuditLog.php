<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceAuditLog extends Model { protected $fillable=['user_id','employee_id','attendance_id','action','old_values','new_values','reason','source','ip_address','user_agent','performed_at']; protected $casts=['old_values'=>'array','new_values'=>'array','performed_at'=>'datetime']; }
