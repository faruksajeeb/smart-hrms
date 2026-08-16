<?php
namespace App\Services\HR;
use App\Models\AttendanceDailyRecord;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceCorrectionHistory;
use App\Models\AttendanceAuditLog;
use App\Models\User;
use App\Services\HR\Approval\ApprovalEngineService;
use Illuminate\Support\Facades\DB;
class AttendanceRegularizationService
{
    public function __construct(protected ApprovalEngineService $approval, protected AttendanceProcessingService $processing, protected AttendancePeriodService $periods, protected AttendanceBackdateService $backdates) {}
    public function create(User $user, array $data): AttendanceRegularization
    {
        return DB::transaction(function () use ($user, $data) {
            $record = AttendanceDailyRecord::findOrFail($data['attendance_daily_record_id']);
            abort_unless((int)$record->user_id === (int)$user->id, 403);
            $this->periods->assertEditable($record->attendance_date->toDateString());
            $this->backdates->validate($record->attendance_date->toDateString(), $user, false, $data['reason'] ?? null);
            if (in_array($record->lifecycle_status, ['finalized','locked'], true)) throw new \RuntimeException('Finalized attendance cannot be regularized.');
            $duplicate = AttendanceRegularization::where('attendance_daily_record_id',$record->id)->whereIn('status',['draft','submitted','pending'])->exists();
            if ($duplicate) throw new \RuntimeException('An active regularization already exists for this attendance date.');
            $regularization = AttendanceRegularization::create(array_merge($data, ['reference_no'=>'AR-'.now()->format('YmdHis').'-'.str_pad((string)random_int(1,9999),4,'0',STR_PAD_LEFT),'user_id'=>$user->id,'attendance_date'=>$record->attendance_date,'status'=>'draft','created_by'=>$user->id]));
            return $regularization;
        });
    }
    public function submit(AttendanceRegularization $regularization, User $user): AttendanceRegularization
    {
        abort_unless((int)$regularization->user_id === (int)$user->id, 403);
        if ($regularization->status !== 'draft') throw new \RuntimeException('Only draft regularizations can be submitted.');
        return DB::transaction(function () use ($regularization, $user) {
            $request = $this->approval->submit($user, 'attendance_regularization', AttendanceRegularization::class, $regularization->id);
            $regularization->update(['status'=>'pending','approval_request_id'=>$request->id,'submitted_at'=>now(),'updated_by'=>$user->id]);
            return $regularization->fresh(['approvalRequest']);
        });
    }
    public function applyApproved(AttendanceRegularization $regularization, int $actorId): void
    {
        $before = $regularization->record?->toArray();
        $regularization->update(['status'=>'approved','approved_in'=>$regularization->requested_in,'approved_out'=>$regularization->requested_out,'approved_status'=>$regularization->requested_status,'approved_at'=>now(),'updated_by'=>$actorId]);
        $after = $this->processing->processEmployeeAttendance($regularization->user, $regularization->attendance_date, $actorId, true);
        AttendanceCorrectionHistory::create(['attendance_daily_record_id'=>$after->id,'user_id'=>$regularization->user_id,'correction_type'=>$regularization->regularization_type,'old_values'=>$before ?: [],'new_values'=>$after->toArray(),'reason'=>$regularization->reason,'source'=>'regularization','corrected_by'=>$actorId,'corrected_at'=>now(),'reference_type'=>self::class,'reference_id'=>$regularization->id]);
        AttendanceAuditLog::create(['user_id'=>$actorId,'employee_id'=>$regularization->user_id,'attendance_id'=>$after->id,'action'=>'attendance_regularized','old_values'=>$before ?: [],'new_values'=>$after->toArray(),'reason'=>$regularization->reason,'source'=>'approval','performed_at'=>now()]);
    }
    public function reject(AttendanceRegularization $regularization, int $actorId, ?string $remarks=null): void { $regularization->update(['status'=>'rejected','remarks'=>$remarks ?: $regularization->remarks,'rejected_at'=>now(),'updated_by'=>$actorId]); }
}
