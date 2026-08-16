<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceException extends Model { protected $fillable=['user_id','attendance_daily_record_id','exception_date','exception_type','severity','status','source','details','resolved_by','resolved_at']; protected $casts=['exception_date'=>'date','resolved_at'=>'datetime']; }
