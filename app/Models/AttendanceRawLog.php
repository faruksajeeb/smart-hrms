<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceRawLog extends Model { protected $fillable=['employee_identifier','user_id','device_id','punch_identifier','punch_datetime','punch_type','source','device_location','raw_payload','imported_at','processing_status','error_message']; protected $casts=['punch_datetime'=>'datetime','raw_payload'=>'array','imported_at'=>'datetime']; }
