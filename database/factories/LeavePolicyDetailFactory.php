<?php

namespace Database\Factories;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeavePolicyDetailFactory extends Factory
{
    protected $model = \App\Models\LeavePolicyDetail::class;

    public function definition(): array
    {
        return [
            'leave_policy_id' => LeavePolicy::factory(),
            'leave_type_id' => LeaveType::factory(),
            'annual_entitlement' => $this->faker->randomFloat(2, 10, 30),
            'accrual_method' => $this->faker->randomElement(['none', 'monthly', 'quarterly', 'yearly']),
            'monthly_accrual' => $this->faker->randomFloat(2, 0.5, 3),
            'carry_forward_allowed' => $this->faker->boolean(),
            'maximum_carry_forward' => $this->faker->optional()->randomFloat(2, 1, 10),
            'encashment_allowed' => $this->faker->boolean(),
            'maximum_encashment' => $this->faker->optional()->randomFloat(2, 1, 15),
            'maximum_consecutive_days' => $this->faker->optional()->numberBetween(5, 30),
            'minimum_days_per_application' => $this->faker->numberBetween(1, 5),
            'maximum_days_per_application' => $this->faker->optional()->numberBetween(5, 30),
            'half_day_allowed' => $this->faker->boolean(70),
            'hourly_leave_allowed' => $this->faker->boolean(30),
            'attachment_required' => $this->faker->boolean(20),
            'medical_certificate_required' => $this->faker->boolean(30),
            'notice_period_days' => $this->faker->numberBetween(0, 7),
            'minimum_service_months' => $this->faker->numberBetween(0, 12),
            'probation_allowed' => $this->faker->boolean(60),
            'include_weekly_off' => $this->faker->boolean(30),
            'include_holiday' => $this->faker->boolean(20),
            'sandwich_rule' => $this->faker->boolean(40),
            'allow_negative_balance' => false,
            'gender_restriction' => $this->faker->randomElement(['male', 'female', 'any']),
            'marital_status_restriction' => $this->faker->randomElement(['single', 'married', 'any']),
            'applicable_after_confirmation' => $this->faker->boolean(30),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
