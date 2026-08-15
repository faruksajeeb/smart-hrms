<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceDailyRecord extends Model
{
    protected $fillable = ['user_id','attendance_date','shift_id','shift_schedule_id','leave_application_id','holiday_id','first_in','last_out','worked_minutes','scheduled_minutes','late_minutes','early_out_minutes','calculated_overtime_minutes','approved_overtime_minutes','attendance_status','day_status','processing_source','lifecycle_status','processed_at','remarks'];
    protected $casts = ['attendance_date'=>'date','first_in'=>'datetime','last_out'=>'datetime','processed_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
    public function schedule(): BelongsTo { return $this->belongsTo(ShiftSchedule::class, 'shift_schedule_id'); }
    public function leaveApplication(): BelongsTo { return $this->belongsTo(LeaveApplication::class); }
    public function holiday(): BelongsTo { return $this->belongsTo(HolidayCalendar::class); }
    public function regularizations() { return $this->hasMany(AttendanceRegularization::class); }
}
