<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveBalanceLedgerFactory extends Factory
{
    protected $model = \App\Models\LeaveBalanceLedger::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'transaction_type' => $this->faker->randomElement([
                'opening',
                'accrual',
                'carry_forward',
                'leave_approved',
                'leave_cancelled',
                'adjustment',
                'encashment',
                'expiry',
            ]),
            'reference_type' => $this->faker->optional()->randomElement([
                \App\Models\LeaveOpeningBalance::class,
                'leave_application',
            ]),
            'reference_id' => $this->faker->optional()->randomNumber(),
            'transaction_date' => $this->faker->date(),
            'days' => $this->faker->randomFloat(2, -5, 5),
            'balance_after' => $this->faker->randomFloat(2, 0, 30),
            'remarks' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
