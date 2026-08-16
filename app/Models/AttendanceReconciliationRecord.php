<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceReconciliationRecord extends Model { protected $fillable=['user_id','attendance_date','issue_type','status','severity','details','context','resolved_by','resolved_at']; protected $casts=['attendance_date'=>'date','context'=>'array','resolved_at'=>'datetime']; }
