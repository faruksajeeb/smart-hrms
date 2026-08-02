<?php

namespace Database\Factories;

use App\Models\LeavePolicy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeavePolicyFactory extends Factory
{
    protected $model = LeavePolicy::class;

    public function definition(): array
    {
        $policies = [
            ['name' => 'Corporate Policy', 'code' => 'CORP'],
            ['name' => 'Factory Policy', 'code' => 'FACT'],
            ['name' => 'Executive Policy', 'code' => 'EXEC'],
            ['name' => 'Contract Employee Policy', 'code' => 'CONT'],
            ['name' => 'Probation Policy', 'code' => 'PROB'],
        ];

        $policy = $this->faker->randomElement($policies);

        return [
            'policy_name' => $policy['name'],
            'policy_code' => $policy['code'] . '-' . $this->faker->unique()->numerify('###'),
            'description' => $this->faker->sentence(),
            'effective_from' => $this->faker->date(),
            'effective_to' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
