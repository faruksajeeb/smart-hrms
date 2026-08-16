<?php
namespace App\Services\HR;
use App\Models\AttendanceAdminSetting;
use Carbon\Carbon;
class AttendanceBackdateService { public function validate(string $date, $user, bool $override=false, ?string $reason=null): void { $setting=AttendanceAdminSetting::first()??new AttendanceAdminSetting(['maximum_backdated_days'=>7,'employee_backdated_regularization'=>true,'backdate_reason_required'=>true]);$days=Carbon::parse($date)->diffInDays(today());if($days>(int)$setting->maximum_backdated_days && !($override&&$user->can('attendance-backdated.override')))abort(422,'This attendance date is outside the allowed backdated correction window.');if($setting->backdate_reason_required&&!$reason&&$days>0)abort(422,'A reason is required for backdated attendance changes.'); } }
