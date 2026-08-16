<?php
namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Models\AttendancePayrollPeriod;
use App\Services\HR\AttendancePayrollService;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AttendancePayrollController extends Controller
{
    public function __construct(protected AttendancePayrollService $service) {}
    public function periods(){return Inertia::render('HR/Attendance/Payroll/Periods',['periods'=>AttendancePayrollPeriod::latest('period_from')->paginate(15)]);}
    public function storePeriod(Request $request){$data=$request->validate(['period_name'=>'required|string|max:100','period_code'=>'required|string|max:50|unique:attendance_payroll_periods,period_code','period_from'=>'required|date','period_to'=>'required|date|after_or_equal:period_from','payroll_cutoff_date'=>'nullable|date','remarks'=>'nullable|string']);$period=$this->service->createPeriod($data,$request->user()->id);return back()->with('success',"Period {$period->period_name} created.");}
    public function summary(Request $request, AttendancePayrollPeriod $period){return Inertia::render('HR/Attendance/Payroll/Summary',['period'=>$period,'summaries'=>$this->service->summaries($period,$request->only(['company_id','branch_id','department_id','employee']))]);}
    public function process(Request $request, AttendancePayrollPeriod $period){$count=$this->service->process($period,$request->user()->id,$request->input('reason'));return back()->with('success',"Generated {$count} payroll attendance summaries.");}
    public function finalize(Request $request, AttendancePayrollPeriod $period){$this->service->finalize($period,$request->user()->id);return back()->with('success','Payroll attendance period finalized.');}
    public function lock(Request $request, AttendancePayrollPeriod $period){$this->service->lock($period,$request->user()->id);return back()->with('success','Payroll attendance period locked.');}
    public function reopen(Request $request, AttendancePayrollPeriod $period){$data=$request->validate(['reason'=>'required|string|min:5|max:2000']);$this->service->reopen($period,$request->user()->id,$data['reason']);return back()->with('success','Payroll attendance period reopened.');}
    public function export(Request $request, AttendancePayrollPeriod $period){return response()->streamDownload(function()use($period){$h=fopen('php://output','w');fputcsv($h,['Employee ID','Employee Name','Payroll Period','Working Days','Payable Days','Unpaid Days','Paid Leave','Unpaid Leave','Late Deduction Days','Early Out Deduction Days','Overtime Hours','Status']);foreach($this->service->export($period) as $row)fputcsv($h,$row);fclose($h);},'attendance-payroll-'.$period->period_code.'.csv',['Content-Type'=>'text/csv']);}
}
