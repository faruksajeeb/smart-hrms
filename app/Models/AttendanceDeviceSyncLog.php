<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceDeviceSyncLog extends Model { protected $fillable=['device_id','sync_type','started_at','completed_at','from_datetime','to_datetime','records_fetched','records_inserted','records_duplicate','records_failed','records_processed','status','error_message','initiated_by']; protected $casts=['started_at'=>'datetime','completed_at'=>'datetime','from_datetime'=>'datetime','to_datetime'=>'datetime']; public function device(){return $this->belongsTo(AttendanceDevice::class);} }
