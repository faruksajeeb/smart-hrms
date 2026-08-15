<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendancePolicyAssignment extends Model { use SoftDeletes; protected $fillable = ['attendance_policy_id','company_id','branch_id','division_id','department_id','section_id','unit_id','designation_id','employment_type','user_id','effective_from','effective_to','status','remarks','created_by','updated_by']; protected function casts(): array { return ['effective_from'=>'date','effective_to'=>'date']; } public function policy(): BelongsTo { return $this->belongsTo(AttendancePolicy::class,'attendance_policy_id'); } public function employee(): BelongsTo { return $this->belongsTo(User::class,'user_id'); } }
