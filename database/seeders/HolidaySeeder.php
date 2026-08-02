<?php

namespace Database\Seeders;

use App\Models\HolidayCalendar;
use App\Models\User;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@smart-hr.com')->first();

        $holidays = [
            [
                'holiday_name' => 'New Year',
                'holiday_code' => 'NY-2024',
                'holiday_date' => '2024-01-01',
                'holiday_type' => 'national',
                'is_recurring' => true,
                'description' => 'New Year Day',
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'holiday_name' => 'Independence Day',
                'holiday_code' => 'ID-2024',
                'holiday_date' => '2024-03-26',
                'holiday_type' => 'national',
                'is_recurring' => true,
                'description' => 'Independence Day',
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'holiday_name' => 'Victory Day',
                'holiday_code' => 'VD-2024',
                'holiday_date' => '2024-12-16',
                'holiday_type' => 'national',
                'is_recurring' => true,
                'description' => 'Victory Day',
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'holiday_name' => 'Eid al-Fitr',
                'holiday_code' => 'EID-F-2024',
                'holiday_date' => '2024-04-11',
                'holiday_type' => 'religious',
                'is_recurring' => false,
                'description' => 'Eid al-Fitr Holiday',
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'holiday_name' => 'Eid al-Adha',
                'holiday_code' => 'EID-A-2024',
                'holiday_date' => '2024-06-17',
                'holiday_type' => 'religious',
                'is_recurring' => false,
                'description' => 'Eid al-Adha Holiday',
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
        ];

        foreach ($holidays as $holiday) {
            HolidayCalendar::create($holiday);
        }
    }
}
