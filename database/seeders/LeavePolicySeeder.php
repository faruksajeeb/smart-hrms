<?php

namespace Database\Seeders;

use App\Models\LeavePolicy;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeavePolicySeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::where('email', 'hr@smart-hr.com')->first();

        $policies = [
            [
                'policy_name' => 'Corporate Policy',
                'policy_code' => 'CORP',
                'description' => 'Standard corporate leave policy for office employees',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'policy_name' => 'Factory Policy',
                'policy_code' => 'FACT',
                'description' => 'Leave policy for factory/floor employees',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'policy_name' => 'Executive Policy',
                'policy_code' => 'EXEC',
                'description' => 'Leave policy for executives and senior management',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'policy_name' => 'Contract Employee Policy',
                'policy_code' => 'CONT',
                'description' => 'Leave policy for contract employees',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
            [
                'policy_name' => 'Probation Policy',
                'policy_code' => 'PROB',
                'description' => 'Leave policy for probationary employees',
                'effective_from' => '2024-01-01',
                'effective_to' => null,
                'status' => 'active',
                'created_by' => $hr?->id,
                'updated_by' => $hr?->id,
            ],
        ];

        foreach ($policies as $policy) {
            LeavePolicy::create($policy);
        }
    }
}
