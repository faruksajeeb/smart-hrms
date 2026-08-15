<?php

namespace App\Services\HR;

use App\Models\AttendanceDailyRecord;
use App\Models\AttendancePunch;
use App\Models\AttendanceProcessingLog;
use App\Models\AttendanceRegularization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceProcessingService
{
    public function __construct(protected AttendanceScheduleResolver $scheduleResolver) {}

    public function processEmployeeAttendance(User $employee, Carbon|string $date, ?int $actorId = null, bool $force = false): AttendanceDailyRecord
    {
        $date = $date instanceof Carbon ? $date->copy()->startOfDay() : Carbon::parse($date)->startOfDay();
        return DB::transaction(function () use ($employee, $date, $actorId, $force) {
            $record = AttendanceDailyRecord::query()->where('user_id', $employee->id)->whereDate('attendance_date', $date)->lockForUpdate()->first();
            if ($record && in_array($record->lifecycle_status, ['finalized', 'locked'], true) && ! $force) {
                throw new \RuntimeException('Attendance is finalized or locked and requires authorized reprocessing.');
            }
            $schedule = $this->scheduleResolver->resolve($employee, $date);
            $rule = $schedule['attendance_policy']?->rules;
            $windowStart = $date->copy()->subDay();
            $windowEnd = $date->copy()->addDay()->endOfDay();
            $punches = AttendancePunch::query()->where('user_id', $employee->id)->whereBetween('punch_datetime', [$windowStart, $windowEnd])->orderBy('punch_datetime')->get()->unique(fn ($p) => $p->punch_datetime?->format('Y-m-d H:i:s'));
            $result = $this->calculate($date, $schedule, $rule, $punches);
            $regularization = AttendanceRegularization::query()->where('user_id', $employee->id)->whereDate('attendance_date', $date)->where('status', 'approved')->latest('approved_at')->first();
            if ($regularization) {
                if ($regularization->approved_in) $result['first_in'] = $regularization->approved_in;
                if ($regularization->approved_out) $result['last_out'] = $regularization->approved_out;
                if ($regularization->approved_status) $result['attendance_status'] = $regularization->approved_status;
                $result = $this->recalculateTimes($result, $date, $schedule, $rule);
                $result['processing_source'] = 'regularization';
            }
            $classification = $schedule['classification'] ?? ($schedule['approved_leave'] ? 'LEAVE' : ($schedule['holiday'] ? 'HOLIDAY' : ($schedule['weekly_off'] ? 'WEEKLY_OFF' : 'WORKING_DAY')));

            $record = AttendanceDailyRecord::updateOrCreate(['user_id' => $employee->id, 'attendance_date' => $date->toDateString()], array_merge($result, [
                'shift_id' => $schedule['shift']?->id,
                'shift_schedule_id' => $schedule['roster']?->id,
                'leave_application_id' => $schedule['approved_leave']?->application?->id,
                'holiday_id' => $schedule['holiday']?->id,
                'day_status' => $classification,
                'lifecycle_status' => $record?->lifecycle_status === 'finalized' ? 'finalized' : ($record?->lifecycle_status === 'locked' ? 'locked' : 'processed'),
                'processed_at' => now(),
                'remarks' => $this->remarks($schedule, $punches),
            ]));
            AttendanceProcessingLog::create(['user_id'=>$employee->id,'processing_date'=>$date->toDateString(),'action'=>$force?'reprocess':'process','status'=>'success','context'=>['record_id'=>$record->id,'punch_count'=>$punches->count()],'created_by'=>$actorId]);
            return $record->fresh(['user','shift','schedule']);
        });
    }

    public function processDate(Carbon|string $date, ?int $actorId = null, ?int $employeeId = null, ?int $companyId = null, bool $force = false): int
    {
        $query = User::query()->where('status', User::STATUS_ACTIVE);
        if ($employeeId) $query->whereKey($employeeId);
        if ($companyId) $query->where('company_id', $companyId);
        $count = 0;
        $query->chunkById(100, function ($employees) use (&$count, $date, $actorId, $force) {
            foreach ($employees as $employee) { $this->processEmployeeAttendance($employee, $date, $actorId, $force); $count++; }
        });
        return $count;
    }

    public function processDateRange(Carbon|string $from, Carbon|string $to, ?int $actorId = null, ?int $employeeId = null, ?int $companyId = null, bool $force = false): int
    {
        $total = 0; $cursor = Carbon::parse($from)->startOfDay(); $end = Carbon::parse($to)->startOfDay();
        while ($cursor->lte($end)) { $total += $this->processDate($cursor, $actorId, $employeeId, $companyId, $force); $cursor->addDay(); }
        return $total;
    }

    protected function calculate(Carbon $date, array $schedule, $rule, $punches): array
    {
        $pairs = []; $open = null;
        foreach ($punches as $punch) {
            $time = $punch->punch_datetime;
            if (strtolower((string) $punch->punch_type) === 'in') { if (! $open) $open = $time; continue; }
            if (strtolower((string) $punch->punch_type) === 'out') { if ($open && $time->gt($open)) { $pairs[] = [$open, $time]; $open = null; } continue; }
            if (! $open) $open = $time; else { $pairs[] = [$open, $time]; $open = null; }
        }
        $first = $pairs[0][0] ?? $open; $last = $pairs ? end($pairs)[1] : null;
        $working = collect($pairs)->sum(fn ($pair) => $pair[0]->diffInMinutes($pair[1]));
        $result = ['first_in'=>$first,'last_out'=>$last,'worked_minutes'=>$working,'scheduled_minutes'=>0,'late_minutes'=>0,'early_out_minutes'=>0,'calculated_overtime_minutes'=>0,'approved_overtime_minutes'=>0,'attendance_status'=>'not_applicable','processing_source'=>'system'];
        if ($schedule['weekly_off']) $result['attendance_status'] = $punches->isEmpty() ? 'weekly_off' : 'present';
        elseif ($schedule['holiday']) $result['attendance_status'] = $punches->isEmpty() ? 'holiday' : 'present';
        elseif ($schedule['approved_leave'] && $schedule['approved_leave']->leave_days < 1) $result['attendance_status'] = $punches->isEmpty() ? 'half_day' : 'present';
        elseif ($schedule['approved_leave']) $result['attendance_status'] = 'leave';
        elseif (! $schedule['shift_start']) $result['attendance_status'] = 'not_applicable';
        elseif (! $first || ! $last) $result['attendance_status'] = $punches->isEmpty() ? 'absent' : 'missing_punch';
        else $result['attendance_status'] = 'present';
        if ($schedule['shift_start'] && $schedule['shift_end']) {
            $start = Carbon::parse($date->toDateString().' '.$schedule['shift_start']); $end = Carbon::parse($date->toDateString().' '.$schedule['shift_end']);
            if ($schedule['cross_midnight'] || $end->lte($start)) $end->addDay();
            $result['scheduled_minutes'] = $start->diffInMinutes($end);
            if ($first) $result['late_minutes'] = max(0, $start->diffInMinutes($first, false) - (int)($rule?->late_grace_minutes ?? 0));
            if ($last && $last->lt($end)) $result['early_out_minutes'] = max(0, $last->diffInMinutes($end) - (int)($rule?->early_out_grace_minutes ?? 0));
            $result['calculated_overtime_minutes'] = max(0, $end->diffInMinutes($last, false));
            if ($rule && ! $rule->overtime_allowed) $result['calculated_overtime_minutes'] = 0;
            if ($rule?->minimum_overtime_minutes && $result['calculated_overtime_minutes'] < $rule->minimum_overtime_minutes) $result['calculated_overtime_minutes'] = 0;
            if ($result['worked_minutes'] > 0 && $rule?->half_day_threshold_hours && $result['worked_minutes'] < $rule->half_day_threshold_hours * 60 && $result['attendance_status'] === 'present') $result['attendance_status'] = 'half_day';
            if ($result['late_minutes'] > (int)($rule?->late_threshold_minutes ?? 0) && $result['early_out_minutes'] > (int)($rule?->early_out_threshold_minutes ?? 0)) $result['attendance_status'] = 'late_early_out';
            elseif ($result['late_minutes'] > (int)($rule?->late_threshold_minutes ?? 0)) $result['attendance_status'] = 'late';
            elseif ($result['early_out_minutes'] > (int)($rule?->early_out_threshold_minutes ?? 0)) $result['attendance_status'] = 'early_out';
        }
        return $result;
    }

    protected function recalculateTimes(array $result, Carbon $date, array $schedule, $rule): array
    { return $this->calculate($date, $schedule, $rule, collect([['punch_datetime'=>$result['first_in'],'punch_type'=>'in'],['punch_datetime'=>$result['last_out'],'punch_type'=>'out']])->filter(fn($p)=>$p['punch_datetime'])->map(fn($p)=>(object)$p)); }
    protected function remarks(array $schedule, $punches): ?string { return implode('; ', array_filter([$schedule['weekly_off']?'Weekly off':null,$schedule['holiday']?'Holiday':null,$schedule['approved_leave']?'Approved leave':null,$punches->isEmpty()?'No raw punches':null])); }
}
