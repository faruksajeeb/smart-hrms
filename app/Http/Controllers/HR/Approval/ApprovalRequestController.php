<?php

namespace App\Http\Controllers\HR\Approval;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\HR\Approval\ApprovalEngineService;
use App\Models\AttendanceRegularization;
use App\Services\HR\AttendanceRegularizationService;
use App\Services\HR\AttendancePeriodService;

class ApprovalRequestController extends Controller
{
    public function approve(Request $request, ApprovalRequest $approvalRequest, ApprovalEngineService $engine, AttendanceRegularizationService $regularizations, AttendancePeriodService $periods)
    {
        if ($approvalRequest->module_name === 'attendance_regularization') $periods->assertEditable(AttendanceRegularization::findOrFail($approvalRequest->reference_id)->attendance_date->toDateString());
        $engine->approve($approvalRequest, $request->user(), $request->input('remarks'));
        $approvalRequest->refresh();
        if ($approvalRequest->module_name === 'attendance_regularization' && $approvalRequest->current_status->value === 'approved') $regularizations->applyApproved(AttendanceRegularization::findOrFail($approvalRequest->reference_id), $request->user()->id);
        return back()->with('success','Approval completed.');
    }
    public function reject(Request $request, ApprovalRequest $approvalRequest, ApprovalEngineService $engine, AttendanceRegularizationService $regularizations)
    {
        $engine->reject($approvalRequest, $request->user(), $request->input('remarks'));
        if ($approvalRequest->module_name === 'attendance_regularization') $regularizations->reject(AttendanceRegularization::findOrFail($approvalRequest->reference_id), $request->user()->id, $request->input('remarks'));
        return back()->with('success','Approval rejected.');
    }
    public function index(Request $request): Response
    {
        $query = ApprovalRequest::query()
            ->with(['workflow', 'requester']);

        if ($request->filled('status')) {
            $query->where('current_status', $request->string('status'));
        }

        if ($request->filled('module')) {
            $query->where('module_name', $request->string('module'));
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('HR/Approval/Requests/Index', [
            'requests' => $requests,
        ]);
    }

    public function show(ApprovalRequest $request): Response
    {
        $request->load([
            'workflow',
            'requester',
            'steps.approver',
            'steps.workflowLevel',
        ]);

        return Inertia::render('HR/Approval/Requests/Show', [
            'request' => $request,
        ]);
    }

    public function pending(Request $request): Response
    {
        $user = $request->user();

        $pendingRequests = ApprovalRequest::query()
            ->with(['workflow', 'requester'])
            ->where('current_status', 'pending')
            ->whereHas('steps', function ($query) use ($user) {
                $query->where('approver_id', $user->id)
                    ->where('status', 'pending');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
// dd($pendingRequests);
        return Inertia::render('HR/Approval/Requests/Pending', [
            'requests' => $pendingRequests,
        ]);
    }

    public function history(Request $request): Response
    {
        $user = $request->user();

        $history = ApprovalRequest::query()
            ->with(['workflow', 'requester'])
            ->where('current_status', '!=', 'pending')
            ->whereHas('steps', function ($query) use ($user) {
                $query->where('approver_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('HR/Approval/Requests/History', [
            'requests' => $history,
        ]);
    }
}
