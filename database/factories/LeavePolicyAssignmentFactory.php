<?php

namespace Database\Factories;

use App\Models\LeavePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeavePolicyAssignmentFactory extends Factory
{
    protected $model = \App\Models\LeavePolicyAssignment::class;

    public function definition(): array
    {
        return [
            'leave_policy_id' => LeavePolicy::factory(),
            'company_id' => $this->faker->optional()->randomElement([1, 2]),
            'branch_id' => $this->faker->optional()->randomElement([1, 2, 3]),
            'division_id' => $this->faker->optional()->randomElement([1, 2]),
            'department_id' => $this->faker->optional()->randomElement([1, 2, 3]),
            'section_id' => $this->faker->optional()->randomElement([1, 2]),
            'unit_id' => $this->faker->optional()->randomElement([1, 2]),
            'designation_id' => $this->faker->optional()->randomElement([1, 2, 3]),
            'employment_type' => $this->faker->optional()->randomElement(['full_time', 'part_time', 'contract']),
            'user_id' => $this->faker->optional()->randomElement([1, 2, 3, 4, 5]),
            'effective_from' => $this->faker->date(),
            'effective_to' => $this->faker->optional()->date(),
            'remarks' => $this->faker->optional()->sentence(),
            'status' => 'active',
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
