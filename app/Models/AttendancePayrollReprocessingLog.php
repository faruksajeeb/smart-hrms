<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendancePayrollReprocessingLog extends Model { protected $fillable=['attendance_payroll_period_id','user_id','action','previous_values','new_values','reason','created_by']; protected $casts=['previous_values'=>'array','new_values'=>'array']; }
