<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceDeviceLog extends Model { protected $fillable=['device_id','external_log_id','employee_identifier','user_id','punch_datetime','punch_type','verification_type','device_timestamp','received_at','raw_payload','normalized_payload','processing_status','processed_at','error_message','retry_count','is_duplicate','fingerprint']; protected $casts=['raw_payload'=>'array','normalized_payload'=>'array','punch_datetime'=>'datetime','device_timestamp'=>'datetime','received_at'=>'datetime','processed_at'=>'datetime','is_duplicate'=>'boolean']; public function device(){return $this->belongsTo(AttendanceDevice::class);} public function user(){return $this->belongsTo(User::class);} }
