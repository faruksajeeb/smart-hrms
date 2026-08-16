<?php
namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Models\AttendanceAuditLog;
use App\Models\AttendanceArchive;
use App\Models\AttendanceCorrectionHistory;
use App\Models\AttendanceException;
use App\Models\AttendanceReconciliationRecord;
use App\Models\AttendanceRawLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AttendanceAdministrationController extends Controller { public function audit(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'audit','rows'=>AttendanceAuditLog::latest('performed_at')->paginate(25)]);} public function corrections(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'corrections','rows'=>AttendanceCorrectionHistory::latest('corrected_at')->paginate(25)]);} public function reconciliation(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'reconciliation','rows'=>AttendanceReconciliationRecord::latest()->paginate(25)]);} public function exceptions(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'exceptions','rows'=>AttendanceException::latest()->paginate(25)]);} public function imports(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'imports','rows'=>AttendanceRawLog::latest()->paginate(25)]);} public function archive(){return Inertia::render('HR/Attendance/Administration/Index',['tab'=>'archive','rows'=>AttendanceArchive::latest('archived_at')->paginate(25)]);} public function resolveReconciliation(Request $request,AttendanceReconciliationRecord $record){$record->update(['status'=>'resolved','resolved_by'=>$request->user()->id,'resolved_at'=>now()]);return back()->with('success','Reconciliation resolved.');} public function resolveException(Request $request,AttendanceException $exception){$exception->update(['status'=>'resolved','resolved_by'=>$request->user()->id,'resolved_at'=>now()]);return back()->with('success','Exception resolved.');} }
