<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        $leaveTypes = [
            ['name' => 'Annual Leave', 'code' => 'AL'],
            ['name' => 'Casual Leave', 'code' => 'CL'],
            ['name' => 'Sick Leave', 'code' => 'SL'],
            ['name' => 'Maternity Leave', 'code' => 'ML'],
            ['name' => 'Paternity Leave', 'code' => 'PL'],
            ['name' => 'Compensatory Leave', 'code' => 'COMP'],
            ['name' => 'Leave Without Pay', 'code' => 'LOP'],
            ['name' => 'Bereavement Leave', 'code' => 'BL'],
        ];

        $type = $this->faker->randomElement($leaveTypes);

        return [
            'leave_name' => $type['name'],
            'leave_code' => $type['code'] . '-' . $this->faker->unique()->numerify('###'),
            'description' => $this->faker->sentence(),
            'is_paid' => $this->faker->boolean(80),
            'display_color' => $this->faker->hexColor(),
            'display_order' => $this->faker->numberBetween(1, 20),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
