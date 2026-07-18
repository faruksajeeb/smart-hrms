<?php

namespace App\Services\HR;

use App\Models\WeeklyOffPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeeklyOffPolicyService
{
    /**
     * Create a weekly off policy.
     */
    public function create(array $data): WeeklyOffPolicy
    {
        return DB::transaction(function () use ($data) {

            $policy = WeeklyOffPolicy::create([
                'company_id' => $data['company_id'],
                'policy_name' => $data['policy_name'],
                'policy_code' => $data['policy_code'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'created_by' => Auth::id(),
            ]);

            $this->syncDays($policy, $data['days']);

            return $policy->load('days');
        });
    }

    /**
     * Update a weekly off policy.
     */
    public function update(
        WeeklyOffPolicy $policy,
        array $data
    ): WeeklyOffPolicy {

        return DB::transaction(function () use ($policy, $data) {

            $policy->update([
                'company_id' => $data['company_id'],
                'policy_name' => $data['policy_name'],
                'policy_code' => $data['policy_code'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'updated_by' => Auth::id(),
            ]);

            $this->syncDays($policy, $data['days']);

            return $policy->fresh('days');
        });
    }

    /**
     * Delete policy.
     */
    public function delete(WeeklyOffPolicy $policy): void
    {
        DB::transaction(function () use ($policy) {

            $policy->days()->delete();

            $policy->delete();
        });
    }

    /**
     * Synchronize weekly off rules.
     */
    protected function syncDays(
        WeeklyOffPolicy $policy,
        array $days
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Enterprise Approach
        |--------------------------------------------------------------------------
        |
        | Delete existing child records
        | Insert latest submitted records
        |
        */

        $policy->days()->delete();

        foreach ($days as $day) {

            $policy->days()->create([

                'day_of_week' => $day['day_of_week'],

                'week_type' => $day['week_type'],

                'week_number' => $day['week_number'] ?? null,

                'off_type' => $day['off_type'],

                'effective_from' =>
                $day['effective_from'] ?? null,

                'effective_to' =>
                $day['effective_to'] ?? null,

                'status' =>
                $day['status'] ?? true,
            ]);
        }
    }


    public function clone(
        WeeklyOffPolicy $policy
    ): WeeklyOffPolicy {

        return DB::transaction(function () use ($policy) {

            $clone = $policy->replicate();

            $clone->policy_name = $policy->policy_name . ' (Copy)';

            $clone->policy_code = $this->generateUniqueCode(
                $policy->policy_code
            );

            $clone->status = false;

            $clone->save();

            foreach ($policy->days as $day) {

                $clone->days()->create([

                    'day_of_week' => $day->day_of_week,

                    'week_type' => $day->week_type,

                    'week_number' => $day->week_number,

                    'off_type' => $day->off_type,

                ]);
            }

            return $clone;
        });
    }
}
