<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceCorrectionHistory extends Model { protected $fillable=['attendance_daily_record_id','user_id','correction_type','old_values','new_values','reason','source','corrected_by','corrected_at','reference_type','reference_id']; protected $casts=['old_values'=>'array','new_values'=>'array','corrected_at'=>'datetime']; }
