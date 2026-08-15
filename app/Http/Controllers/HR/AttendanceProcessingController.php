<?php
namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Models\AttendanceDailyRecord;
use App\Models\User;
use App\Services\HR\AttendanceProcessingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AttendanceProcessingController extends Controller
{
    public function __construct(protected AttendanceProcessingService $service) {}
    public function index(Request $request) { $query=AttendanceDailyRecord::with(['user','shift'])->latest('attendance_date'); if($request->filled('from'))$query->whereDate('attendance_date','>=',$request->from); if($request->filled('to'))$query->whereDate('attendance_date','<=',$request->to); if($request->filled('status'))$query->where('attendance_status',$request->status); return Inertia::render('HR/Attendance/Processing/Index',['records'=>$query->paginate(25)->withQueryString(),'statuses'=>['present','absent','late','early_out','late_early_out','half_day','weekly_off','holiday','leave','missing_punch','not_applicable']]); }
    public function process(Request $request) { $data=$request->validate(['date'=>'nullable|date','from'=>'nullable|date','to'=>'nullable|date','employee'=>'nullable|integer','company'=>'nullable|integer','force'=>'nullable|boolean']); $from=$data['from']??$data['date']??today()->toDateString(); $to=$data['to']??$from; $count=$this->service->processDateRange($from,$to,$request->user()->id,$data['employee']??null,$data['company']??null,(bool)($data['force']??false)); return back()->with('success',"Processed {$count} attendance record(s)."); }
    public function reprocess(Request $request, AttendanceDailyRecord $record) { $this->service->processEmployeeAttendance($record->user,$record->attendance_date,$request->user()->id,true); return back()->with('success','Attendance reprocessed.'); }
    public function finalize(Request $request, AttendanceDailyRecord $record) { $record->update(['lifecycle_status'=>'finalized']); return back()->with('success','Attendance finalized.'); }
}
