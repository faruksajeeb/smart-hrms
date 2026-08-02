<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Services\HR\Leave\LeavePolicyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreLeavePolicyRequest;
use App\Http\Requests\HR\Leave\UpdateLeavePolicyRequest;

class LeavePolicyController extends Controller
{
    public function __construct(
        protected LeavePolicyService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = LeavePolicy::query();

        if ($request->filled('search')) {
            $query->where('policy_name', 'like', '%' . $request->string('search') . '%')
                ->orWhere('policy_code', 'like', '%' . $request->string('search') . '%');
        }

        $policies = $query->orderBy('policy_name')->paginate(15);

        return Inertia::render('HR/Leave/LeavePolicies/Index', [
            'policies' => $policies,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/Leave/LeavePolicies/Create');
    }

    public function store(StoreLeavePolicyRequest $request)
    {
        $validated = $request->validated();

        $this->service->create($validated);

        return redirect()
            ->route('hr.leave.policies.index')
            ->with('success', 'Leave policy created successfully.');
    }

    public function show(LeavePolicy $leavePolicy): Response
    {
        $leavePolicy->load(['details.leaveType', 'creator', 'updater']);

        return Inertia::render('HR/Leave/LeavePolicies/Show', [
            'policy' => $leavePolicy,
        ]);
    }

    public function edit(LeavePolicy $leavePolicy): Response
    {
        $leavePolicy->load('details.leaveType');

        return Inertia::render('HR/Leave/LeavePolicies/Edit', [
            'policy' => $leavePolicy,
        ]);
    }

    public function update(UpdateLeavePolicyRequest $request, LeavePolicy $leavePolicy)
    {
        $validated = $request->validated();

        $this->service->update($leavePolicy, $validated);

        return redirect()
            ->route('hr.leave.policies.index')
            ->with('success', 'Leave policy updated successfully.');
    }

    public function destroy(LeavePolicy $leavePolicy)
    {
        $this->service->delete($leavePolicy);

        return back()->with('success', 'Leave policy deleted successfully.');
    }
}
