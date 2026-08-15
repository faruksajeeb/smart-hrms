<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceRegularization extends Model
{
    protected $fillable = ['reference_no','user_id','attendance_daily_record_id','attendance_date','regularization_type','requested_in','requested_out','requested_status','approved_in','approved_out','approved_status','reason','remarks','status','approval_request_id','submitted_at','approved_at','rejected_at','cancelled_at','created_by','updated_by'];
    protected $casts = ['attendance_date'=>'date','requested_in'=>'datetime','requested_out'=>'datetime','approved_in'=>'datetime','approved_out'=>'datetime','submitted_at'=>'datetime','approved_at'=>'datetime','rejected_at'=>'datetime','cancelled_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function record(): BelongsTo { return $this->belongsTo(AttendanceDailyRecord::class, 'attendance_daily_record_id'); }
    public function approvalRequest(): BelongsTo { return $this->belongsTo(ApprovalRequest::class); }
}
