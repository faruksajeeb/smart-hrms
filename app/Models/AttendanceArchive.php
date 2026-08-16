<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceArchive extends Model { protected $fillable=['attendance_daily_record_id','user_id','attendance_date','record_data','organization_snapshot','archived_by','archived_at']; protected $casts=['attendance_date'=>'date','record_data'=>'array','organization_snapshot'=>'array','archived_at'=>'datetime']; }
