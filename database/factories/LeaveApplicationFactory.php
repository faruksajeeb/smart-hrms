<?php

namespace Database\Factories;

use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveApplicationFactory extends Factory
{
    protected $model = LeaveApplication::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+2 weeks');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 5) . ' days');

        return [
            'application_no' => 'LA' . date('Ymd') . str_pad($this->faker->unique()->randomNumber(6), 6, '0', STR_PAD_LEFT),
            'user_id' => User::factory(),
            'leave_type_id' => \App\Models\LeaveType::factory(),
            'leave_policy_id' => \App\Models\LeavePolicy::factory(),
            'application_type' => $this->faker->randomElement(['normal', 'emergency']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_days' => $this->faker->randomFloat(2, 1, 10),
            'requested_days' => $this->faker->randomFloat(2, 1, 10),
            'is_half_day' => $this->faker->boolean(10),
            'half_day_session' => $this->faker->randomElement(['morning', 'afternoon']),
            'is_emergency' => $this->faker->boolean(10),
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'pending', 'approved', 'rejected', 'cancelled', 'withdrawn']),
            'submitted_at' => $this->faker->optional()->dateTime(),
            'cancelled_at' => $this->faker->optional()->dateTime(),
            'withdrawn_at' => $this->faker->optional()->dateTime(),
            'approved_at' => $this->faker->optional()->dateTime(),
            'rejected_at' => $this->faker->optional()->dateTime(),
            'remarks' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
