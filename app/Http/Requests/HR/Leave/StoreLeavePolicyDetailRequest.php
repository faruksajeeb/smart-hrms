<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeavePolicyDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
                    "leave_type_id" => [
                "required",
                "integer",
                "exists:leave_types,id",
                function ($attribute, $value, $fail) {
                    $policyId = request()->route("leave_policy")->id ?? request()->input("leave_policy_id");
                    $exists = \App\Models\LeavePolicyDetail::where("leave_policy_id", $policyId)
                        ->where("leave_type_id", $value)
                        ->when(request()->isMethod("PUT") || request()->isMethod("PATCH"), function ($query) {
                            $detailId = request()->route("detail")->id ?? null;
                            if ($detailId) {
                                $query->where("id", "!=", $detailId);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        $fail("This leave type is already configured for this policy.");
                    }
                },
            ],
            "annual_entitlement" => ["nullable", "numeric", "min:0"],
            "accrual_method" => ["required", "in:none,monthly,quarterly,yearly"],
            "monthly_accrual" => ["nullable", "numeric", "min:0"],
            "carry_forward_allowed" => ["boolean"],
            "maximum_carry_forward" => ["nullable", "numeric", "min:0"],
            "encashment_allowed" => ["boolean"],
            "maximum_encashment" => ["nullable", "numeric", "min:0"],
            "maximum_consecutive_days" => ["nullable", "integer", "min:1"],
            "minimum_days_per_application" => ["required", "integer", "min:1"],
            "maximum_days_per_application" => ["nullable", "integer", "min:1"],
            "half_day_allowed" => ["boolean"],
            "hourly_leave_allowed" => ["boolean"],
            "attachment_required" => ["boolean"],
            "medical_certificate_required" => ["boolean"],
            "notice_period_days" => ["required", "integer", "min:0"],
            "minimum_service_months" => ["required", "integer", "min:0"],
            "probation_allowed" => ["boolean"],
            "include_weekly_off" => ["boolean"],
            "include_holiday" => ["boolean"],
            "sandwich_rule" => ["boolean"],
            "allow_negative_balance" => ["boolean"],
            "gender_restriction" => ["required", "in:male,female,any"],
            "marital_status_restriction" => ["required", "in:single,married,any"],
            "applicable_after_confirmation" => ["boolean"],
            "status" => ["required", "in:active,inactive"],
        ];
    }
}
