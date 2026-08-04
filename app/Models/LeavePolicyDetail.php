<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\AccrualMethod;
use App\Enums\GenderRestriction;
use App\Enums\LeavePolicyDetailStatus;
use App\Enums\MaritalStatusRestriction;

#[Fillable([
    'leave_policy_id',
    'leave_type_id',
    'annual_entitlement',
    'accrual_method',
    'monthly_accrual',
    'carry_forward_allowed',
    'maximum_carry_forward',
    'encashment_allowed',
    'maximum_encashment',
    'maximum_consecutive_days',
    'minimum_days_per_application',
    'maximum_days_per_application',
    'half_day_allowed',
    'hourly_leave_allowed',
    'attachment_required',
    'attachment_required_after_days',
    'attachment_document_type',
    'maximum_attachment_files',
    'maximum_attachment_size_mb',
    'allowed_extensions',
    'medical_certificate_required',
    'delegate_required',
    'delegate_acknowledgement_required',
    'notice_period_days',
    'minimum_service_months',
    'probation_allowed',
    'include_weekly_off',
    'include_holiday',
    'sandwich_rule',
    'allow_negative_balance',
    'gender_restriction',
    'marital_status_restriction',
    'applicable_after_confirmation',
    'status',
    'created_by',
    'updated_by',
])]
class LeavePolicyDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'annual_entitlement' => 'decimal:2',
            'monthly_accrual' => 'decimal:2',
            'maximum_carry_forward' => 'decimal:2',
            'maximum_encashment' => 'decimal:2',
            'maximum_consecutive_days' => 'integer',
            'minimum_days_per_application' => 'integer',
            'maximum_days_per_application' => 'integer',
            'notice_period_days' => 'integer',
            'minimum_service_months' => 'integer',
            'carry_forward_allowed' => 'boolean',
            'encashment_allowed' => 'boolean',
            'half_day_allowed' => 'boolean',
            'hourly_leave_allowed' => 'boolean',
            'attachment_required' => 'boolean',
            'attachment_required_after_days' => 'decimal:2',
            'maximum_attachment_files' => 'integer',
            'maximum_attachment_size_mb' => 'integer',
            'medical_certificate_required' => 'boolean',
            'delegate_required' => 'boolean',
            'delegate_acknowledgement_required' => 'boolean',
            'probation_allowed' => 'boolean',
            'include_weekly_off' => 'boolean',
            'include_holiday' => 'boolean',
            'sandwich_rule' => 'boolean',
            'allow_negative_balance' => 'boolean',
            'applicable_after_confirmation' => 'boolean',
            'accrual_method' => AccrualMethod::class,
            'gender_restriction' => GenderRestriction::class,
            'marital_status_restriction' => MaritalStatusRestriction::class,
            'status' => LeavePolicyDetailStatus::class,
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
