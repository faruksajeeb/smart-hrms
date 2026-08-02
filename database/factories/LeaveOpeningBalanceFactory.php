<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveOpeningBalanceFactory extends Factory
{
    protected $model = \App\Models\LeaveOpeningBalance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'opening_balance' => $this->faker->randomFloat(2, 0, 30),
            'effective_date' => $this->faker->date(),
            'remarks' => $this->faker->optional()->sentence(),
            'reason' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
