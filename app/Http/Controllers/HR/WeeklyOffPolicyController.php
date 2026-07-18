<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreWeeklyOffPolicyRequest;
use App\Http\Requests\HR\UpdateWeeklyOffPolicyRequest;
use App\Models\MasterDataItem;
use App\Models\WeeklyOffPolicy;
use App\Services\HR\WeeklyOffPolicyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WeeklyOffPolicyController extends Controller
{
    public function __construct(
        protected WeeklyOffPolicyService $service
    ) {}

    /**
     * Display Weekly Off Policies
     */
    public function index(): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $policies = WeeklyOffPolicy::query()
            ->with('company')
            ->withCount('days')

            ->when(request('search'), function ($query) {
                $query->where(function ($q) {

                    $q->where('policy_name', 'like', '%' . request('search') . '%')
                        ->orWhere('policy_code', 'like', '%' . request('search') . '%');
                });
            })

            ->when(request('company'), function ($query) {
                $query->where('company_id', request('company'));
            })

            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })

            ->latest()

            ->paginate(15)

            ->withQueryString();

        return Inertia::render(
            'HR/WeeklyOffPolicies/Index',
            [

                'policies' => $policies,

                'companies' => MasterDataItem::query()
                    ->where('category', MasterDataItem::CATEGORY_COMPANY)
                    ->where('status', MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'filters' => [

                    'search' => request('search'),

                    'company' => request('company'),

                    'status' => request('status'),

                ],

            ]
        );
    }

    public function create(): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        return Inertia::render(
            'HR/WeeklyOffPolicies/Create',
            [

                'companies' => MasterDataItem::query()
                    ->where('category', MasterDataItem::CATEGORY_COMPANY)
                    ->where('status', MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

            ]
        );
    }

    /**
     * Store Policy
     */
    public function store(
        StoreWeeklyOffPolicyRequest $request
    ): RedirectResponse {

        $this->service->create(
            $request->validated()
        );

        return back()->with(
            'success',
            'Weekly off policy created successfully.'
        );
    }

    /**
     * Edit Policy
     */
    public function edit(WeeklyOffPolicy $weeklyOffPolicy): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $weeklyOffPolicy->load('days', 'company');

        $days = collect(range(0, 6))->map(function ($day) use ($weeklyOffPolicy) {

            $rule = $weeklyOffPolicy->days
                ->firstWhere('day_of_week', $day);

            return [

                'day_of_week' => $day,

                'label' => [
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                ][$day],

                'enabled' => $rule !== null,

                'week_type' => $rule?->week_type ?? 'every',

                'week_number' => $rule?->week_number,

                'off_type' => $rule?->off_type ?? 'full_day',

            ];
        });

        return Inertia::render(
            'HR/WeeklyOffPolicies/Edit',
            [

                'policy' => [

                    'id' => $weeklyOffPolicy->id,

                    'company_id' => $weeklyOffPolicy->company_id,

                    'company_name' => $weeklyOffPolicy->company?->name,

                    'policy_name' => $weeklyOffPolicy->policy_name,

                    'policy_code' => $weeklyOffPolicy->policy_code,

                    'description' => $weeklyOffPolicy->description,

                    'status' => (bool) $weeklyOffPolicy->status,

                    'days' => $days,

                ],

                'companies' => MasterDataItem::query()
                    ->where('category', MasterDataItem::CATEGORY_COMPANY)
                    ->where('status', MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

            ]
        );
    }
    /**
     * Update Policy
     */
    public function update(
        UpdateWeeklyOffPolicyRequest $request,
        WeeklyOffPolicy $weeklyOffPolicy
    ): RedirectResponse {

        $this->service->update(
            $weeklyOffPolicy,
            $request->validated()
        );

        return back()->with(
            'success',
            'Weekly off policy updated successfully.'
        );
    }



    public function clone(WeeklyOffPolicy $weeklyOffPolicy): Response
    {
        abort_unless(auth()->user()->can('manage attendance'), 403);

        $weeklyOffPolicy->load('days', 'company');

        $days = collect(range(0, 6))->map(function ($day) use ($weeklyOffPolicy) {

            $rule = $weeklyOffPolicy->days
                ->firstWhere('day_of_week', $day);

            return [

                'day_of_week' => $day,

                'label' => [
                    'Sunday',
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                ][$day],

                'enabled' => $rule !== null,

                'week_type' => $rule?->week_type ?? 'every',

                'week_number' => $rule?->week_number,

                'off_type' => $rule?->off_type ?? 'full_day',

            ];

        });

        return Inertia::render(
            'HR/WeeklyOffPolicies/Create',
            [

                'companies' => MasterDataItem::query()
                    ->where('category', MasterDataItem::CATEGORY_COMPANY)
                    ->where('status', MasterDataItem::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name']),

                'clonePolicy' => [

                    'company_id' => $weeklyOffPolicy->company_id,

                    'company_name' => $weeklyOffPolicy->company?->name,

                    'policy_name' => $weeklyOffPolicy->policy_name.' (Copy)',

                    'policy_code' => $weeklyOffPolicy->policy_code.'-COPY',

                    'description' => $weeklyOffPolicy->description,

                    'status' => false,

                    'days' => $days,

                ],

            ]
        );
    }

    /**
     * Delete Policy
     */
    public function destroy(
        WeeklyOffPolicy $weeklyOffPolicy
    ): RedirectResponse {

        abort_unless(auth()->user()->can('manage attendance'), 403);

        /*
        |--------------------------------------------------------------------------
        | Enterprise Validation
        |--------------------------------------------------------------------------
        */

        if (
            $weeklyOffPolicy->employeeAssignments()->exists()
        ) {

            return back()->withErrors([
                'error' => 'This policy is already assigned to employees.'
            ]);
        }

        $this->service->delete(
            $weeklyOffPolicy
        );

        return back()->with(
            'success',
            'Weekly off policy deleted successfully.'
        );
    }
}
