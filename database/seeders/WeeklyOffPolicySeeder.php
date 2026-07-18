<?php

namespace Database\Seeders;

use App\Models\MasterDataItem;
use App\Models\WeeklyOffPolicy;
use App\Models\WeeklyOffPolicyDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeeklyOffPolicySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $company = MasterDataItem::where('category', MasterDataItem::CATEGORY_COMPANY)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->first();

            if (! $company) {
                $this->command->warn('No active company found. Weekly Off Seeder skipped.');

                return;
            }

            $policies = [

                [
                    'policy_name' => 'Corporate Weekend',
                    'policy_code' => 'CORP-001',
                    'description' => 'Friday and Saturday weekly off.',
                    'days' => [
                        [
                            'day_of_week' => 5,
                            'week_type' => 'every',
                            'off_type' => 'full_day',
                        ],
                        [
                            'day_of_week' => 6,
                            'week_type' => 'every',
                            'off_type' => 'full_day',
                        ],
                    ],
                ],

                [
                    'policy_name' => 'Factory Weekly Off',
                    'policy_code' => 'FACT-001',
                    'description' => 'Friday weekly off.',
                    'days' => [
                        [
                            'day_of_week' => 5,
                            'week_type' => 'every',
                            'off_type' => 'full_day',
                        ],
                    ],
                ],

                [
                    'policy_name' => 'Retail Weekly Off',
                    'policy_code' => 'SHOP-001',
                    'description' => 'Sunday weekly off.',
                    'days' => [
                        [
                            'day_of_week' => 0,
                            'week_type' => 'every',
                            'off_type' => 'full_day',
                        ],
                    ],
                ],

                [
                    'policy_name' => 'Alternate Saturday',
                    'policy_code' => 'ALT-SAT',
                    'description' => 'Friday every week and 2nd & 4th Saturday.',
                    'days' => [

                        [
                            'day_of_week' => 5,
                            'week_type' => 'every',
                            'off_type' => 'full_day',
                        ],

                        [
                            'day_of_week' => 6,
                            'week_type' => 'specific',
                            'week_number' => 2,
                            'off_type' => 'full_day',
                        ],

                        [
                            'day_of_week' => 6,
                            'week_type' => 'specific',
                            'week_number' => 4,
                            'off_type' => 'full_day',
                        ],

                    ],
                ],

                [
                    'policy_name' => 'Six Day Operation',
                    'policy_code' => '6DAY',
                    'description' => 'Friday first half only.',
                    'days' => [
                        [
                            'day_of_week' => 5,
                            'week_type' => 'every',
                            'off_type' => 'first_half',
                        ],
                    ],
                ],

            ];

            foreach ($policies as $item) {

                $policy = WeeklyOffPolicy::updateOrCreate(

                    [
                        'company_id' => $company->id,
                        'policy_code' => $item['policy_code'],
                    ],

                    [
                        'policy_name' => $item['policy_name'],
                        'description' => $item['description'],
                        'status' => true,
                    ]

                );

                $policy->days()->delete();

                foreach ($item['days'] as $day) {

                    WeeklyOffPolicyDay::create([

                        'weekly_off_policy_id' => $policy->id,

                        'day_of_week' => $day['day_of_week'],

                        'week_type' => $day['week_type'],

                        'week_number' => $day['week_number'] ?? null,

                        'off_type' => $day['off_type'],

                    ]);

                }
            }
        });
    }
}