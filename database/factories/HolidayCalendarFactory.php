<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayCalendarFactory extends Factory
{
    protected $model = \App\Models\HolidayCalendar::class;

    public function definition(): array
    {
        $holidays = [
            ['name' => 'New Year', 'code' => 'NY', 'type' => 'national'],
            ['name' => 'Independence Day', 'code' => 'ID', 'type' => 'national'],
            ['name' => 'Eid al-Fitr', 'code' => 'EID-F', 'type' => 'religious'],
            ['name' => 'Eid al-Adha', 'code' => 'EID-A', 'type' => 'religious'],
            ['name' => 'Company Foundation Day', 'code' => 'CFD', 'type' => 'company'],
        ];

        $holiday = $this->faker->randomElement($holidays);

        return [
            'holiday_name' => $holiday['name'],
            'holiday_code' => $holiday['code'] . '-' . $this->faker->unique()->numerify('###'),
            'holiday_date' => $this->faker->date(),
            'holiday_type' => $holiday['type'],
            'is_recurring' => $this->faker->boolean(50),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
