<?php

namespace App\Services\HR\Leave;

use App\Models\HolidayCalendar;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\User;
use App\Models\EmployeeWeeklyOffAssignment;
use Illuminate\Support\Facades\DB;

class LeaveCalculationService
{
    public function calculateDays(
        User $employee,
        LeavePolicy $policy,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        bool $isHalfDay = false,
        ?string $halfDaySession = null,
        bool $isEmergency = false
    ): array {
        $days = [];
        $period = new \DatePeriod(
            \Carbon\Carbon::parse($startDate),
            \Carbon\Carbon::parse($endDate)->addDay(),
            new \DateInterval('P1D')
        );

        $holidays = $this->getHolidays($employee, $startDate, $endDate);
        $weeklyOffDays = $this->getWeeklyOffDays($employee, $startDate, $endDate);
        $sandwichRule = $this->shouldApplySandwichRule($policy);

        $previousWorkingDay = null;
        $nextWorkingDay = null;

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = (int) $date->format('N');

            $isHoliday = isset($holidays[$dateStr]);
            $isWeeklyOff = isset($weeklyOffDays[$dateStr]);
            $isWeekend = in_array($dayOfWeek, [6, 7]);

            $dayType = 'full_day';
            $session = null;
            $countsAsLeave = true;

            if ($isHalfDay) {
                $dayType = 'half_day';
                $session = $halfDaySession;
                $countsAsLeave = true;
            } elseif ($isHoliday || $isWeeklyOff || $isWeekend) {
                $countsAsLeave = false;
            }

            if ($sandwichRule && !$isHalfDay) {
                if ($previousWorkingDay === null && $this->isWorkingDay($employee, $dateStr, $holidays, $weeklyOffDays)) {
                    $previousWorkingDay = $dateStr;
                }
                if ($nextWorkingDay === null && $this->isWorkingDay($employee, $dateStr, $holidays, $weeklyOffDays)) {
                    $nextWorkingDay = $dateStr;
                }
            }

            $leaveDays = $countsAsLeave ? ($isHalfDay ? 0.5 : 1) : 0;

            $days[] = [
                'leave_date' => $dateStr,
                'day_type' => $dayType,
                'session' => $session,
                'is_holiday' => $isHoliday,
                'is_weekly_off' => $isWeeklyOff || $isWeekend,
                'counts_as_leave' => $countsAsLeave,
                'leave_days' => $leaveDays,
                'remarks' => $isHoliday ? ($holidays[$dateStr] ?? 'Holiday') : ($isWeeklyOff ? 'Weekly Off' : null),
            ];
        }

        return $days;
    }

    public function getHolidays(User $employee, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $holidays = HolidayCalendar::whereBetween('holiday_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'active')
            ->get();

        $applicable = [];
        foreach ($holidays as $holiday) {
            $scope = $holiday->scopes()->where('status', 'active')->first();
            if (!$scope) {
                continue;
            }

            $scopeType = $scope->scope_type ?? 'company';
            $scopeId = $scope->scope_id ?? null;

            if ($scopeType === 'company' && $scopeId == $employee->company_id) {
                $applicable[$holiday->holiday_date->format('Y-m-d')] = $holiday->holiday_name;
            } elseif ($scopeType === 'branch' && $scopeId == $employee->branch_id) {
                $applicable[$holiday->holiday_date->format('Y-m-d')] = $holiday->holiday_name;
            }
        }

        return $applicable;
    }

    public function getWeeklyOffDays(User $employee, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $assignment = EmployeeWeeklyOffAssignment::where('user_id', $employee->id)
            ->where('is_current', true)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return [];
        }

        $weeklyOffDays = [];
        $offPatterns = $assignment->off_patterns ?? [];

        foreach ($offPatterns as $pattern) {
            $dayOfWeek = $pattern['day_of_week'] ?? null;
            if (!$dayOfWeek) {
                continue;
            }

            $period = new \DatePeriod(
                \Carbon\Carbon::parse($startDate),
                \Carbon\Carbon::parse($endDate)->addDay(),
                new \DateInterval('P1D')
            );

            foreach ($period as $date) {
                if ((int) $date->format('N') === (int) $dayOfWeek) {
                    $weeklyOffDays[$date->format('Y-m-d')] = 'Weekly Off';
                }
            }
        }

        return $weeklyOffDays;
    }

    public function shouldApplySandwichRule(LeavePolicy $policy): bool
    {
        $rule = $policy->details()
            ->where('status', 'active')
            ->where('rule_type', 'sandwich_rule')
            ->first();

        return $rule ? (bool) $rule->rule_value : false;
    }

    private function isWorkingDay(User $employee, string $dateStr, array $holidays, array $weeklyOffDays): bool
    {
        $dayOfWeek = (int) \Carbon\Carbon::parse($dateStr)->format('N');
        return !isset($holidays[$dateStr]) && !isset($weeklyOffDays[$dateStr]) && !in_array($dayOfWeek, [6, 7]);
    }
}
