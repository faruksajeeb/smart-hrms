<?php

namespace Database\Seeders;

use App\Models\LeaveOpeningBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeaveOpeningBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@smart-hr.com')->first();

        $employees = User::whereHas('roles', function ($query) {
            $query->where('name', 'employee');
        })->limit(5)->get();

        $annual = LeaveType::where('leave_code', 'AL')->first();
        $casual = LeaveType::where('leave_code', 'CL')->first();

        $service = new \App\Services\HR\Leave\LeaveBalanceService();

        foreach ($employees as $employee) {
            if ($annual) {
                $balance = LeaveOpeningBalance::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => $annual->id,
                    'opening_balance' => 15,
                    'effective_date' => '2024-01-01',
                    'remarks' => 'Opening balance imported from legacy system',
                    'reason' => 'Implementation',
                    'created_by' => $hr?->id,
                    'updated_by' => $hr?->id,
                ]);

                $service->createLedgerEntry([
                    'user_id' => $employee->id,
                    'leave_type_id' => $annual->id,
                    'transaction_type' => 'opening',
                    'reference_type' => LeaveOpeningBalance::class,
                    'reference_id' => $balance->id,
                    'transaction_date' => '2024-01-01',
                    'days' => 15,
                    'balance_after' => 15,
                    'remarks' => 'Opening balance imported from legacy system',
                ]);
            }

            if ($casual) {
                $balance = LeaveOpeningBalance::create([
                    'user_id' => $employee->id,
                    'leave_type_id' => $casual->id,
                    'opening_balance' => 5,
                    'effective_date' => '2024-01-01',
                    'remarks' => 'Opening balance imported from legacy system',
                    'reason' => 'Implementation',
                    'created_by' => $hr?->id,
                    'updated_by' => $hr?->id,
                ]);

                $service->createLedgerEntry([
                    'user_id' => $employee->id,
                    'leave_type_id' => $casual->id,
                    'transaction_type' => 'opening',
                    'reference_type' => LeaveOpeningBalance::class,
                    'reference_id' => $balance->id,
                    'transaction_date' => '2024-01-01',
                    'days' => 5,
                    'balance_after' => 5,
                    'remarks' => 'Opening balance imported from legacy system',
                ]);
            }
        }
    }
}
