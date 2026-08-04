<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveDelegateController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = LeaveApplication::query()
            ->where('delegate_user_id', $user->id)
            ->with(['employee', 'leaveType', 'leavePolicy']);

        if ($request->filled('status')) {
            $query->where('delegate_status', $request->string('status'));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        $statuses = \App\Enums\DelegateStatus::options();

        return Inertia::render('Employee/Leave/Delegations/Index', [
            'applications' => $applications,
            'statuses' => $statuses,
            'filters' => [
                'status' => $request->string('status'),
            ],
        ]);
    }

    public function show(LeaveApplication $application): Response
    {
        $user = request()->user();

        if ($application->delegate_user_id !== $user->id) {
            abort(403);
        }

        $application->load(['employee', 'leaveType', 'leavePolicy', 'days', 'attachments']);

        return Inertia::render('Employee/Leave/Delegations/Show', [
            'application' => $application,
        ]);
    }

    public function accept(Request $request, LeaveApplication $application)
    {
        $user = $request->user();

        if ($application->delegate_user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'delegate_status' => \App\Enums\DelegateStatus::Accepted,
            'delegate_remarks' => $request->string('remarks'),
            'delegate_responded_at' => now(),
            'updated_by' => $user->id,
        ]);

        $application->employee->notify(new \App\Notifications\DelegateAcceptedNotification($application->fresh()));

        return back()->with('success', 'Delegation accepted successfully.');
    }

    public function decline(Request $request, LeaveApplication $application)
    {
        $user = $request->user();

        if ($application->delegate_user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $application->update([
            'delegate_status' => \App\Enums\DelegateStatus::Declined,
            'delegate_remarks' => $request->string('remarks'),
            'delegate_responded_at' => now(),
            'updated_by' => $user->id,
        ]);

        $application->employee->notify(new \App\Notifications\DelegateDeclinedNotification($application->fresh()));

        return back()->with('success', 'Delegation declined successfully.');
    }
}
