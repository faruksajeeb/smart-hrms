<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Services\HR\AttendanceReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceReportController extends Controller
{
    public function __construct(protected AttendanceReportService $reports) {}
    public function index(Request $request, string $type='daily') { abort_unless(in_array($type, AttendanceReportService::TYPES, true), 404); $permission=$type==='daily'?'attendance.reports.daily':'attendance.reports.'.str_replace('-','_',$type); abort_unless($request->user()->can($permission) || $request->user()->can('attendance.reports.view'),403); return Inertia::render('HR/Attendance/Reports/Index',$this->reports->report($type,$request->validate($this->rules()),$request->user())+['type'=>$type]); }
    public function export(Request $request, string $type='daily') { abort_unless($request->user()->can('attendance.reports.export'),403); $filters=$request->validate($this->rules()); $headers=['Employee ID','Employee Name','Company','Branch','Department','Shift','Attendance Date','First In','Last Out','Worked Hours','Late Minutes','Early Out Minutes','Overtime Hours','Status','Regularization','Remarks']; return response()->streamDownload(function() use($type,$filters,$request,$headers){$handle=fopen('php://output','w');fputcsv($handle,$headers);foreach($this->reports->export($type,$filters,$request->user()) as $row)fputcsv($handle,$row);fclose($handle);},'attendance-'.$type.'-report.csv',['Content-Type'=>'text/csv']); }
    protected function rules(): array { return ['from'=>'nullable|date','to'=>'nullable|date','month'=>'nullable|integer|min:1|max:12','year'=>'nullable|integer|min:2000|max:2200','company_id'=>'nullable|integer','branch_id'=>'nullable|integer','division_id'=>'nullable|integer','department_id'=>'nullable|integer','section_id'=>'nullable|integer','unit_id'=>'nullable|integer','designation_id'=>'nullable|integer','employment_type_id'=>'nullable|integer','employee'=>'nullable|integer','employee_id'=>'nullable|string|max:50','shift_id'=>'nullable|integer','status'=>'nullable|string|max:40','per_page'=>'nullable|integer|min:10|max:100']; }
}
