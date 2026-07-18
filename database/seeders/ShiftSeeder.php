<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $companyId = 1;
        $userId = 1;

        $shifts = [
            [
                'shift_name' => 'General',
                'shift_code' => 'GEN',
                'description' => 'General office shift',

                'start_time' => '09:00:00',
                'end_time' => '18:00:00',

                'break_start' => '13:00:00',
                'break_end' => '14:00:00',

                'grace_time' => 15,
                'working_hours' => 8.00,
                'late_after' => 15,
                'half_day_after' => 240,
                'minimum_work_hours' => 8.00,

                'is_flexible' => false,
                'is_night_shift' => false,

                'color' => '#3B82F6',
                'status' => true,
            ],

            [
                'shift_name' => 'Morning',
                'shift_code' => 'MOR',
                'description' => 'Morning shift',

                'start_time' => '08:00:00',
                'end_time' => '16:00:00',

                'break_start' => '12:00:00',
                'break_end' => '13:00:00',

                'grace_time' => 10,
                'working_hours' => 8.00,
                'late_after' => 10,
                'half_day_after' => 240,
                'minimum_work_hours' => 8.00,

                'is_flexible' => false,
                'is_night_shift' => false,

                'color' => '#10B981',
                'status' => true,
            ],

            [
                'shift_name' => 'Evening',
                'shift_code' => 'EVE',
                'description' => 'Evening shift',

                'start_time' => '14:00:00',
                'end_time' => '22:00:00',

                'break_start' => '18:00:00',
                'break_end' => '19:00:00',

                'grace_time' => 10,
                'working_hours' => 8.00,
                'late_after' => 10,
                'half_day_after' => 240,
                'minimum_work_hours' => 8.00,

                'is_flexible' => false,
                'is_night_shift' => false,

                'color' => '#F59E0B',
                'status' => true,
            ],

            [
                'shift_name' => 'Night',
                'shift_code' => 'NGT',
                'description' => 'Night shift',

                'start_time' => '22:00:00',
                'end_time' => '06:00:00',

                'break_start' => '02:00:00',
                'break_end' => '03:00:00',

                'grace_time' => 10,
                'working_hours' => 8.00,
                'late_after' => 10,
                'half_day_after' => 240,
                'minimum_work_hours' => 8.00,

                'is_flexible' => false,
                'is_night_shift' => true,

                'color' => '#6366F1',
                'status' => true,
            ],

            [
                'shift_name' => 'Flexible',
                'shift_code' => 'FLEX',
                'description' => 'Flexible working shift',

                'start_time' => null,
                'end_time' => null,

                'break_start' => null,
                'break_end' => null,

                'grace_time' => 0,
                'working_hours' => 8.00,
                'late_after' => 0,
                'half_day_after' => 0,
                'minimum_work_hours' => 8.00,

                'is_flexible' => true,
                'is_night_shift' => false,

                'color' => '#6B7280',
                'status' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            DB::table('shifts')->updateOrInsert(
                [
                    'company_id' => $companyId,
                    'shift_code' => $shift['shift_code'],
                ],
                array_merge($shift, [
                    'company_id' => $companyId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}