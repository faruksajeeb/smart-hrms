<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendancePayrollPeriod extends Model { protected $fillable=['period_name','period_code','period_from','period_to','payroll_cutoff_date','status','locked_at','locked_by','reopened_at','reopened_by','remarks','created_by','updated_by']; protected $casts=['period_from'=>'date','period_to'=>'date','payroll_cutoff_date'=>'date','locked_at'=>'datetime','reopened_at'=>'datetime']; public function summaries(){return $this->hasMany(AttendancePayrollSummary::class,'attendance_payroll_period_id');} public function isLocked():bool{return $this->status==='locked';} }
