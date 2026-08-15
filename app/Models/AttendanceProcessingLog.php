<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceProcessingLog extends Model { protected $fillable=['user_id','processing_date','action','status','message','context','created_by']; protected $casts=['processing_date'=>'date','context'=>'array']; }
