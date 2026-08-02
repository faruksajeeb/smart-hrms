<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\HR\Leave\LeaveTypeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreLeaveTypeRequest;
use App\Http\Requests\HR\Leave\UpdateLeaveTypeRequest;

class LeaveTypeController extends Controller
{
    public function __construct(
        protected LeaveTypeService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = LeaveType::query();

        if ($request->filled('search')) {
            $query->where('leave_name', 'like', '%' . $request->string('search') . '%')
                ->orWhere('leave_code', 'like', '%' . $request->string('search') . '%');
        }

        $leaveTypes = $query->orderBy('display_order')->orderBy('leave_name')->paginate(15);

        return Inertia::render('HR/Leave/LeaveTypes/Index', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/Leave/LeaveTypes/Create');
    }

    public function store(StoreLeaveTypeRequest $request)
    {
        $validated = $request->validated();

        $this->service->create($validated);

        return redirect()
            ->route('hr.leave.types.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function show(LeaveType $leaveType): Response
    {
        $leaveType->load('creator', 'updater');

        return Inertia::render('HR/Leave/LeaveTypes/Show', [
            'leaveType' => $leaveType,
        ]);
    }

    public function edit(LeaveType $leaveType): Response
    {
        return Inertia::render('HR/Leave/LeaveTypes/Edit', [
            'leaveType' => $leaveType,
        ]);
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType)
    {
        $validated = $request->validated();

        $this->service->update($leaveType, $validated);

        return redirect()
            ->route('hr.leave.types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->service->delete($leaveType);

        return back()->with('success', 'Leave type deleted successfully.');
    }
}
