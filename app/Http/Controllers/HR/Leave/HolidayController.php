<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\HolidayCalendar;
use App\Services\HR\Leave\HolidayService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreHolidayRequest;
use App\Http\Requests\HR\Leave\UpdateHolidayRequest;

class HolidayController extends Controller
{
    public function __construct(
        protected HolidayService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = HolidayCalendar::query();

        if ($request->filled('search')) {
            $query->where('holiday_name', 'like', '%' . $request->string('search') . '%')
                ->orWhere('holiday_code', 'like', '%' . $request->string('search') . '%');
        }

        if ($request->filled('type')) {
            $query->where('holiday_type', $request->string('type'));
        }

        $holidays = $query->orderBy('holiday_date', 'desc')->paginate(15);

        return Inertia::render('HR/Leave/Holidays/Index', [
            'holidays' => $holidays,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/Leave/Holidays/Create');
    }

    public function store(StoreHolidayRequest $request)
    {
        $validated = $request->validated();

        $holiday = $this->service->create($validated);

        if (!empty($validated['scopes'])) {
            foreach ($validated['scopes'] as $scope) {
                $holiday->scopes()->create($scope);
            }
        }

        return redirect()
            ->route('hr.leave.holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    public function show(HolidayCalendar $holiday): Response
    {
        $holiday->load(['scopes', 'creator', 'updater']);

        return Inertia::render('HR/Leave/Holidays/Show', [
            'holiday' => $holiday,
        ]);
    }

    public function edit(HolidayCalendar $holiday): Response
    {
        $holiday->load('scopes');

        return Inertia::render('HR/Leave/Holidays/Edit', [
            'holiday' => $holiday,
        ]);
    }

    public function update(UpdateHolidayRequest $request, HolidayCalendar $holiday)
    {
        $validated = $request->validated();

        $this->service->update($holiday, $validated);

        if (array_key_exists('scopes', $validated)) {
            $holiday->scopes()->delete();
            
            if (!empty($validated['scopes'])) {
                foreach ($validated['scopes'] as $scope) {
                    $holiday->scopes()->create($scope);
                }
            }
        }

        return redirect()
            ->route('hr.leave.holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    public function destroy(HolidayCalendar $holiday)
    {
        $this->service->delete($holiday);

        return back()->with('success', 'Holiday deleted successfully.');
    }
}
