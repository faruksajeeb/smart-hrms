<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Services\HR\Leave\LeavePolicyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreLeavePolicyDetailRequest;
use App\Http\Requests\HR\Leave\UpdateLeavePolicyDetailRequest;

class LeavePolicyDetailController extends Controller
{
    public function __construct(
        protected LeavePolicyService $policyService
    ) {}

    public function index(LeavePolicy $leavePolicy): Response
    {
        $leavePolicy->load('details.leaveType');

        return Inertia::render('HR/Leave/LeavePolicyDetails/Index', [
            'policy' => $leavePolicy,
        ]);
    }

    public function create(LeavePolicy $leavePolicy): Response
    {
        return Inertia::render('HR/Leave/LeavePolicyDetails/Create', [
            'policy' => $leavePolicy,
        ]);
    }

    public function store(StoreLeavePolicyDetailRequest $request, LeavePolicy $leavePolicy)
    {
        $validated = $request->validated();
        $validated['leave_policy_id'] = $leavePolicy->id;

        $detail = $leavePolicy->details()->create($validated);

        return redirect()
            ->route('hr.leave.policies.details.index', $leavePolicy)
            ->with('success', 'Policy detail created successfully.');
    }

    public function edit(LeavePolicy $leavePolicy, LeavePolicyDetail $detail): Response
    {
        return Inertia::render('HR/Leave/LeavePolicyDetails/Edit', [
            'policy' => $leavePolicy,
            'detail' => $detail,
        ]);
    }

    public function update(UpdateLeavePolicyDetailRequest $request, LeavePolicy $leavePolicy, LeavePolicyDetail $detail)
    {
        $validated = $request->validated();

        $detail->update($validated);

        return redirect()
            ->route('hr.leave.policies.details.index', $leavePolicy)
            ->with('success', 'Policy detail updated successfully.');
    }

    public function destroy(LeavePolicy $leavePolicy, LeavePolicyDetail $detail)
    {
        $detail->delete();

        return back()->with('success', 'Policy detail deleted successfully.');
    }
}
