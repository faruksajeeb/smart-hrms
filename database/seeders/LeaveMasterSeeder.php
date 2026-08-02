<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveMasterSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@smart-hr.com')->first();

        $leaveTypes = [
            [
                'leave_name' => 'Annual Leave',
                'leave_code' => 'AL',
                'description' => 'Annual vacation leave',
                'is_paid' => true,
                'display_color' => '#3B82F6',
                'display_order' => 1,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Casual Leave',
                'leave_code' => 'CL',
                'description' => 'Casual leave for personal reasons',
                'is_paid' => true,
                'display_color' => '#10B981',
                'display_order' => 2,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Sick Leave',
                'leave_code' => 'SL',
                'description' => 'Medical leave',
                'is_paid' => true,
                'display_color' => '#EF4444',
                'display_order' => 3,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Maternity Leave',
                'leave_code' => 'ML',
                'description' => 'Maternity leave for female employees',
                'is_paid' => true,
                'display_color' => '#EC4899',
                'display_order' => 4,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Paternity Leave',
                'leave_code' => 'PL',
                'description' => 'Paternity leave for male employees',
                'is_paid' => true,
                'display_color' => '#6366F1',
                'display_order' => 5,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Compensatory Leave',
                'leave_code' => 'COMP',
                'description' => 'Compensatory off for extra hours worked',
                'is_paid' => true,
                'display_color' => '#F59E0B',
                'display_order' => 6,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Leave Without Pay',
                'leave_code' => 'LOP',
                'description' => 'Leave without pay',
                'is_paid' => false,
                'display_color' => '#6B7280',
                'display_order' => 7,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'leave_name' => 'Bereavement Leave',
                'leave_code' => 'BL',
                'description' => 'Leave for bereavement',
                'is_paid' => true,
                'display_color' => '#8B5CF6',
                'display_order' => 8,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::create($type);
        }
    }
}
