<?php

namespace Database\Seeders;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeavePolicyDetailSeeder extends Seeder
{
    public function run(): void
    {
        $corporate = LeavePolicy::where('policy_code', 'CORP')->first();
        $factory = LeavePolicy::where('policy_code', 'FACT')->first();
        $executive = LeavePolicy::where('policy_code', 'EXEC')->first();
        $contract = LeavePolicy::where('policy_code', 'CONT')->first();
        $probation = LeavePolicy::where('policy_code', 'PROB')->first();

        $annual = LeaveType::where('leave_code', 'AL')->first();
        $casual = LeaveType::where('leave_code', 'CL')->first();
        $sick = LeaveType::where('leave_code', 'SL')->first();
        $maternity = LeaveType::where('leave_code', 'ML')->first();
        $paternity = LeaveType::where('leave_code', 'PL')->first();
        $compensatory = LeaveType::where('leave_code', 'COMP')->first();
        $lop = LeaveType::where('leave_code', 'LOP')->first();
        $bereavement = LeaveType::where('leave_code', 'BL')->first();

        if ($corporate && $annual) {
            $corporate->details()->create([
                'leave_type_id' => $annual->id,
                'annual_entitlement' => 21,
                'accrual_method' => 'yearly',
                'monthly_accrual' => 1.75,
                'carry_forward_allowed' => true,
                'maximum_carry_forward' => 5,
                'encashment_allowed' => true,
                'maximum_encashment' => 10,
                'maximum_consecutive_days' => 15,
                'minimum_days_per_application' => 1,
                'maximum_days_per_application' => 15,
                'half_day_allowed' => true,
                'hourly_leave_allowed' => false,
                'attachment_required' => false,
                'medical_certificate_required' => false,
                'notice_period_days' => 3,
                'minimum_service_months' => 3,
                'probation_allowed' => true,
                'include_weekly_off' => false,
                'include_holiday' => false,
                'sandwich_rule' => false,
                'allow_negative_balance' => false,
                'gender_restriction' => 'any',
                'marital_status_restriction' => 'any',
                'applicable_after_confirmation' => false,
                'status' => 'active',
            ]);
        }

        if ($corporate && $casual) {
            $corporate->details()->create([
                'leave_type_id' => $casual->id,
                'annual_entitlement' => 12,
                'accrual_method' => 'yearly',
                'monthly_accrual' => 1,
                'carry_forward_allowed' => false,
                'maximum_carry_forward' => null,
                'encashment_allowed' => false,
                'maximum_encashment' => null,
                'maximum_consecutive_days' => 3,
                'minimum_days_per_application' => 1,
                'maximum_days_per_application' => 3,
                'half_day_allowed' => true,
                'hourly_leave_allowed' => false,
                'attachment_required' => false,
                'medical_certificate_required' => false,
                'notice_period_days' => 1,
                'minimum_service_months' => 0,
                'probation_allowed' => true,
                'include_weekly_off' => false,
                'include_holiday' => false,
                'sandwich_rule' => false,
                'allow_negative_balance' => false,
                'gender_restriction' => 'any',
                'marital_status_restriction' => 'any',
                'applicable_after_confirmation' => false,
                'status' => 'active',
            ]);
        }

        if ($corporate && $sick) {
            $corporate->details()->create([
                'leave_type_id' => $sick->id,
                'annual_entitlement' => 10,
                'accrual_method' => 'yearly',
                'monthly_accrual' => 0.83,
                'carry_forward_allowed' => false,
                'maximum_carry_forward' => null,
                'encashment_allowed' => false,
                'maximum_encashment' => null,
                'maximum_consecutive_days' => 5,
                'minimum_days_per_application' => 1,
                'maximum_days_per_application' => 5,
                'half_day_allowed' => true,
                'hourly_leave_allowed' => true,
                'attachment_required' => false,
                'medical_certificate_required' => true,
                'notice_period_days' => 0,
                'minimum_service_months' => 0,
                'probation_allowed' => true,
                'include_weekly_off' => false,
                'include_holiday' => false,
                'sandwich_rule' => false,
                'allow_negative_balance' => false,
                'gender_restriction' => 'any',
                'marital_status_restriction' => 'any',
                'applicable_after_confirmation' => false,
                'status' => 'active',
            ]);
        }

        if ($factory && $annual) {
            $factory->details()->create([
                'leave_type_id' => $annual->id,
                'annual_entitlement' => 18,
                'accrual_method' => 'yearly',
                'monthly_accrual' => 1.5,
                'carry_forward_allowed' => true,
                'maximum_carry_forward' => 3,
                'encashment_allowed' => true,
                'maximum_encashment' => 5,
                'maximum_consecutive_days' => 10,
                'minimum_days_per_application' => 1,
                'maximum_days_per_application' => 10,
                'half_day_allowed' => false,
                'hourly_leave_allowed' => false,
                'attachment_required' => false,
                'medical_certificate_required' => false,
                'notice_period_days' => 7,
                'minimum_service_months' => 6,
                'probation_allowed' => false,
                'include_weekly_off' => false,
                'include_holiday' => false,
                'sandwich_rule' => true,
                'allow_negative_balance' => false,
                'gender_restriction' => 'any',
                'marital_status_restriction' => 'any',
                'applicable_after_confirmation' => true,
                'status' => 'active',
            ]);
        }
    }
}
