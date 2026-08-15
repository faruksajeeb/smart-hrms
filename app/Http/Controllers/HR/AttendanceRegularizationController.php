<?php
namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreAttendanceRegularizationRequest;
use App\Models\AttendanceDailyRecord;
use App\Models\AttendanceRegularization;
use App\Services\HR\AttendanceRegularizationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AttendanceRegularizationController extends Controller
{
    public function __construct(protected AttendanceRegularizationService $service) {}
    public function index(Request $request) { $items=AttendanceRegularization::with('record')->where('user_id',$request->user()->id)->latest()->paginate(15)->withQueryString(); return Inertia::render('HR/Attendance/Regularization/Index',['regularizations'=>$items]); }
    public function create(Request $request) { $records=AttendanceDailyRecord::where('user_id',$request->user()->id)->latest('attendance_date')->limit(60)->get(); return Inertia::render('HR/Attendance/Regularization/Create',['records'=>$records]); }
    public function store(StoreAttendanceRegularizationRequest $request) { $item=$this->service->create($request->user(),$request->validated()); return redirect()->route('hr.attendance.regularizations.show',$item)->with('success','Regularization draft created.'); }
    public function show(Request $request, AttendanceRegularization $regularization) { abort_unless((int)$regularization->user_id === (int)$request->user()->id || $request->user()->can('attendance.approval.view'),403); return Inertia::render('HR/Attendance/Regularization/Show',['regularization'=>$regularization->load(['record','approvalRequest.steps'])]); }
    public function submit(Request $request, AttendanceRegularization $regularization) { $this->service->submit($regularization,$request->user()); return back()->with('success','Regularization submitted for approval.'); }
    public function cancel(Request $request, AttendanceRegularization $regularization) { abort_unless((int)$regularization->user_id === (int)$request->user()->id,403); abort_if(in_array($regularization->status,['approved','rejected','cancelled']),422); $regularization->update(['status'=>'cancelled','cancelled_at'=>now(),'updated_by'=>$request->user()->id]); return back()->with('success','Regularization cancelled.'); }
}
