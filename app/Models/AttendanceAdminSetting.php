<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceAdminSetting extends Model { protected $fillable=['maximum_backdated_days','employee_backdated_regularization','manager_backdated_approval','hr_can_bypass_backdate','locked_period_bypass','backdate_reason_required','archive_after_years']; protected $casts=['employee_backdated_regularization'=>'boolean','manager_backdated_approval'=>'boolean','hr_can_bypass_backdate'=>'boolean','locked_period_bypass'=>'boolean']; }
