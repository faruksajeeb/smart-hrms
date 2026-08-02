<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeavePolicyDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_policy_id' => ['required', 'integer', 'exists:leave_policies,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'annual_entitlement' => ['nullable', 'numeric', 'min:0'],
            'accrual_method' => ['required', 'in:none,monthly,quarterly,yearly'],
            'monthly_accrual' => ['nullable', 'numeric', 'min:0'],
            'carry_forward_allowed' => ['boolean'],
            'maximum_carry_forward' => ['nullable', 'numeric', 'min:0'],
            'encashment_allowed' => ['boolean'],
            'maximum_encashment' => ['nullable', 'numeric', 'min:0'],
            'maximum_consecutive_days' => ['nullable', 'integer', 'min:1'],
            'minimum_days_per_application' => ['required', 'integer', 'min:1'],
            'maximum_days_per_application' => ['nullable', 'integer', 'min:1'],
            'half_day_allowed' => ['boolean'],
            'hourly_leave_allowed' => ['boolean'],
            'attachment_required' => ['boolean'],
            'medical_certificate_required' => ['boolean'],
            'notice_period_days' => ['required', 'integer', 'min:0'],
            'minimum_service_months' => ['required', 'integer', 'min:0'],
            'probation_allowed' => ['boolean'],
            'include_weekly_off' => ['boolean'],
            'include_holiday' => ['boolean'],
            'sandwich_rule' => ['boolean'],
            'allow_negative_balance' => ['boolean'],
            'gender_restriction' => ['required', 'in:male,female,any'],
            'marital_status_restriction' => ['required', 'in:single,married,any'],
            'applicable_after_confirmation' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
