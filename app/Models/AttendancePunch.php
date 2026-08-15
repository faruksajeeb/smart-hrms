<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendancePunch extends Model
{
    protected $fillable = ['user_id','punch_datetime','punch_date','punch_type','source','device_id','external_reference','latitude','longitude','remarks'];
    protected $casts = ['punch_datetime'=>'datetime','punch_date'=>'date'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
