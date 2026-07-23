<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\RejoinEmployeeRequest;
use App\Http\Requests\HR\ExtractCvRequest;
use App\Http\Requests\HR\StoreEmployeeRequest;
use App\Http\Requests\HR\TerminateEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Models\EmployeeLifecycleEvent;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\EmployeeShiftAssignment;
use App\Models\EmployeeWeeklyOffAssignment;
use App\Models\EmployeeReportingManagerAssignment;
use App\Models\MasterDataItem;
use App\Models\Shift;
use App\Models\User;
use App\Models\WeeklyOffPolicy;
use App\Services\CvExtractionService;
use App\Services\EmployeeMasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeMasterDataService $employeeMasterData,
        protected CvExtractionService $cvExtraction,
    ) {}

    public function extractCv(ExtractCvRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->cvExtraction->extract($request->file('cv'), (int) $request->user()->id);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage() ?: 'Unable to read this CV. Please try another file.',
            ], 422);
        }

        return response()->json([
            'data' => $data,
            'master_data' => $this->masterDataSuggestions($data),
        ]);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $department = trim((string) $request->string('department'));

        $employees = User::query()
            ->with([
                'employeeProfile',
                'roles:id,name',
                'employeeDocuments' => fn ($query) => $query->where('document_type', 'photo'),
            ])
            ->where(function ($query) {
                $query->whereHas('employeeProfile')
                    ->orWhereHas('roles', fn ($roles) => $roles->where('name', User::ROLE_EMPLOYEE));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhereHas('employeeProfile', function ($profile) use ($search) {
                            $profile->where('department', 'like', "%{$search}%")
                                ->orWhere('designation', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn ($query) => $query->whereHas(
                'employeeProfile',
                fn ($profile) => $profile->where('employment_status', $status),
            ))
            ->when($department !== '', fn ($query) => $query->whereHas(
                'employeeProfile',
                fn ($profile) => $profile->where('department', $department),
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $employee) => $this->employeeSummary($employee));

        return Inertia::render('HR/Employees/Index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'department' => $department,
            ],
            'stats' => $this->stats(),
            'options' => $this->options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('HR/Employees/Create', [
            'options' => $this->options(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->employeeMasterData->validateSelections($request->all());

        $employee = DB::transaction(function () use ($request) {
            $employee = User::create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'employee_id' => $request->string('employee_id')->toString(),
                'password' => Hash::make($request->string('password')->toString()),
                'status' => User::STATUS_ACTIVE,
            ]);

            $employee->assignRole(User::ROLE_EMPLOYEE);
            $employee->employeeProfile()->create($this->profilePayload($request));
            $this->employeeMasterData->syncTags($employee, $request->all());
            $this->storeDocuments($employee, $request);

            if ($request->filled('shift_id')) {
                EmployeeShiftAssignment::create([
                    'user_id' => $employee->id,
                    'shift_id' => $request->shift_id,
                    'effective_from' => $request->date('joining_date'),
                    'effective_to' => null,
                    'assignment_type' => 'initial',
                    'is_current' => true,
                    'remarks' => null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            if ($request->filled('weekly_off_policy_id')) {
                EmployeeWeeklyOffAssignment::create([
                    'user_id' => $employee->id,
                    'weekly_off_policy_id' => $request->weekly_off_policy_id,
                    'effective_from' => $request->date('joining_date'),
                    'effective_to' => null,
                    'assignment_type' => 'initial',
                    'is_current' => true,
                    'remarks' => null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            if ($request->filled('manager_id')) {
                EmployeeReportingManagerAssignment::create([
                    'user_id' => $employee->id,
                    'manager_id' => $request->manager_id,
                    'effective_from' => $request->date('joining_date'),
                    'effective_to' => null,
                    'assignment_type' => 'initial',
                    'is_current' => true,
                    'remarks' => null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }

            $this->recordEvent($employee, EmployeeLifecycleEvent::TYPE_ONBOARDING, 'Employee onboarded', $request->date('joining_date'), [
                'department' => $request->input('department'),
                'designation' => $request->input('designation'),
                'employment_status' => $request->input('employment_status'),
                'master_data_item_ids' => $employee->masterDataItems()->pluck('master_data_items.id')->all(),
            ]);

            return $employee;
        });

        return to_route('hr.employees.show', $employee)
            ->with('success', "{$employee->name} has been onboarded successfully.");
    }

    public function show(User $employee): Response
    {
        $employee->load([
            'employeeProfile',
            'employeeLifecycleEvents.creator:id,name',
            'masterDataItems',
            'shiftAssignments' => fn ($q) => $q->where('is_current', true),
            'shiftAssignments.shift',
            'weeklyOffAssignments' => fn ($q) => $q->where('is_current', true),
            'weeklyOffAssignments.weeklyOffPolicy',
            'weeklyOffAssignments.weeklyOffPolicy.days',
            'reportingManagerAssignments' => fn ($q) => $q->where('is_current', true),
            'reportingManagerAssignments.manager',
        ]);
        $this->loadDocumentsIfAvailable($employee);

        // dd($this->employeeDetail($employee));

        return Inertia::render('HR/Employees/Show', [
            'employee' => $this->employeeDetail($employee),
            'events' => $employee->employeeLifecycleEvents
                ->sortByDesc('created_at')
                ->values()
                ->map(fn (EmployeeLifecycleEvent $event) => [
                    'id' => $event->id,
                    'type' => $event->type,
                    'title' => $event->title,
                    'notes' => $event->notes,
                    'effective_on' => $event->effective_on?->toDateString(),
                    'created_at' => $event->created_at?->format('M d, Y h:i A'),
                    'created_by' => $event->creator?->name,
                ]),
        ]);
    }

    public function edit(User $employee): Response
    {
        $employee->load(['employeeProfile', 'masterDataItems']);
        $this->loadDocumentsIfAvailable($employee);

        return Inertia::render('HR/Employees/Edit', [
            'employee' => $this->employeeDetail($employee),
            'options' => $this->options(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, User $employee): RedirectResponse
    {
        $this->employeeMasterData->validateSelections($request->all());

        DB::transaction(function () use ($request, $employee) {
            $employee->fill([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'employee_id' => $request->string('employee_id')->toString(),
            ]);

            if ($request->filled('password')) {
                $employee->password = Hash::make($request->string('password')->toString());
            }

            $employee->save();
            $employee->syncRoles(array_values(array_unique([
                ...$employee->getRoleNames()->reject(fn (string $role) => $role === User::ROLE_EMPLOYEE)->all(),
                User::ROLE_EMPLOYEE,
            ])));

            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                $this->profilePayload($request),
            );

            $this->employeeMasterData->syncTags($employee, $request->all());

            if ($request->filled('manager_id')) {
                $currentAssignment = EmployeeReportingManagerAssignment::query()
                    ->where('user_id', $employee->id)
                    ->where('is_current', true)
                    ->first();

                if ($currentAssignment && (int) $currentAssignment->manager_id !== (int) $request->integer('manager_id')) {
                    $currentAssignment->update([
                        'effective_to' => now()->subDay(),
                        'is_current' => false,
                        'updated_by' => auth()->id(),
                    ]);
                }

                if (! $currentAssignment || (int) $currentAssignment->manager_id !== (int) $request->integer('manager_id')) {
                    EmployeeReportingManagerAssignment::create([
                        'user_id' => $employee->id,
                        'manager_id' => $request->integer('manager_id'),
                        'effective_from' => now(),
                        'effective_to' => null,
                        'assignment_type' => 'initial',
                        'is_current' => true,
                        'remarks' => 'Updated via employee edit',
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                }
            }

            $this->storeDocuments($employee, $request);

            $this->recordSetupEvents($employee, $request);
        });

        return to_route('hr.employees.show', $employee)
            ->with('success', "{$employee->name} has been updated successfully.");
    }

    public function downloadDocument(User $employee, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $employee->id, 404);

        $disk = config('filesystems.document_analyses_disk', 'local');
        abort_unless(Storage::disk($disk)->exists($document->file), 404);

        return Storage::disk($disk)->download(
            $document->file,
            $document->original_filename ?: basename($document->file),
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream'],
        );
    }

    public function viewDocument(User $employee, EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $employee->id, 404);

        $disk = config('filesystems.document_analyses_disk', 'local');
        abort_unless(Storage::disk($disk)->exists($document->file), 404);

        return Storage::disk($disk)->response(
            $document->file,
            $document->original_filename ?: basename($document->file),
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream'],
        );
    }

    public function terminate(TerminateEmployeeRequest $request, User $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                [
                    'employment_status' => $request->string('employment_status')->toString(),
                    'termination_date' => $request->date('termination_date'),
                    'termination_type' => $request->string('termination_type')->toString(),
                    'termination_reason' => $request->string('termination_reason')->toString(),
                ],
            );

            $employee->forceFill(['status' => User::STATUS_INACTIVE])->save();

            $this->recordEvent(
                $employee,
                EmployeeLifecycleEvent::TYPE_TERMINATION,
                $request->string('employment_status')->toString() === EmployeeProfile::STATUS_LEFT ? 'Employee marked as left' : 'Employee terminated',
                $request->date('termination_date'),
                [
                    'termination_type' => $request->input('termination_type'),
                    'termination_reason' => $request->input('termination_reason'),
                ],
                $request->string('termination_reason')->toString(),
            );
        });

        return to_route('hr.employees.show', $employee)
            ->with('success', "{$employee->name}'s separation has been recorded.");
    }

    public function rejoin(RejoinEmployeeRequest $request, User $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                [
                    'employment_status' => EmployeeProfile::STATUS_REJOINED,
                    'department' => $request->filled('department') ? $request->string('department')->toString() : $employee->employeeProfile?->department,
                    'designation' => $request->filled('designation') ? $request->string('designation')->toString() : $employee->employeeProfile?->designation,
                    'last_rejoined_on' => $request->date('last_rejoined_on'),
                    'termination_date' => null,
                    'termination_type' => null,
                    'termination_reason' => null,
                ],
            );

            $employee->forceFill(['status' => User::STATUS_ACTIVE])->save();

            $this->recordEvent(
                $employee,
                EmployeeLifecycleEvent::TYPE_REJOIN,
                'Employee rejoined',
                $request->date('last_rejoined_on'),
                [
                    'department' => $request->input('department'),
                    'designation' => $request->input('designation'),
                ],
                $request->input('notes'),
            );
        });

        return to_route('hr.employees.show', $employee)
            ->with('success', "{$employee->name} has been rejoined and reactivated.");
    }

    public function transfer(Request $request, User $employee): RedirectResponse
    {
        $validated = $request->validate([
            'department_master_data_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'department' => ['nullable', 'string', 'max:255'],
            'effective_on' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($employee, $validated) {
            $update = ['updated_by' => auth()->id()];

            if ($validated['department_master_data_id'] !== null) {
                $item = MasterDataItem::find($validated['department_master_data_id']);
                $update['department'] = $item?->name;
            } elseif ($validated['department'] !== null) {
                $update['department'] = $validated['department'];
            }

            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                $update
            );

            $this->recordEvent(
                $employee,
                EmployeeLifecycleEvent::TYPE_TRANSFER,
                'Department transferred',
                $validated['effective_on'],
                [
                    'department' => $update['department'] ?? null,
                    'master_data_item_id' => $validated['department_master_data_id'] ?? null,
                ],
                $validated['remarks'],
            );
        });

        return back()->with('success', "{$employee->name}'s department transfer has been recorded.");
    }

    public function promote(Request $request, User $employee): RedirectResponse
    {
        $validated = $request->validate([
            'designation_master_data_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'designation' => ['nullable', 'string', 'max:255'],
            'effective_on' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($employee, $validated) {
            $update = ['updated_by' => auth()->id()];

            if ($validated['designation_master_data_id'] !== null) {
                $item = MasterDataItem::find($validated['designation_master_data_id']);
                $update['designation'] = $item?->name;
            } elseif ($validated['designation'] !== null) {
                $update['designation'] = $validated['designation'];
            }

            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                $update
            );

            $this->recordEvent(
                $employee,
                EmployeeLifecycleEvent::TYPE_PROMOTION,
                'Employee promoted',
                $validated['effective_on'],
                [
                    'designation' => $update['designation'] ?? null,
                    'master_data_item_id' => $validated['designation_master_data_id'] ?? null,
                ],
                $validated['remarks'],
            );
        });

        return back()->with('success', "{$employee->name}'s promotion has been recorded.");
    }

    public function increment(Request $request, User $employee): RedirectResponse
    {
        $validated = $request->validate([
            'salary_amount' => ['required', 'numeric', 'min:0'],
            'effective_on' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($employee, $validated) {
            $employee->employeeProfile()->updateOrCreate(
                ['user_id' => $employee->id],
                [
                    'salary_amount' => $validated['salary_amount'],
                    'updated_by' => auth()->id(),
                ]
            );

            $this->recordEvent(
                $employee,
                EmployeeLifecycleEvent::TYPE_INCREMENT,
                'Salary incremented',
                $validated['effective_on'],
                [
                    'salary_amount' => $validated['salary_amount'],
                ],
                $validated['remarks'],
            );
        });

        return back()->with('success', "{$employee->name}'s salary increment has been recorded.");
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeeSummary(User $employee): array
    {
        $profile = $employee->employeeProfile;

        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'employee_id' => $employee->employee_id,
            'account_status' => $employee->status,
            'employment_status' => $profile?->employment_status ?? 'not_setup',
            'department' => $profile?->department,
            'designation' => $profile?->designation,
            'joining_date' => $profile?->joining_date?->toDateString(),
            'probation_ends_on' => $profile?->probation_ends_on?->toDateString(),
            'salary' => $profile?->salary_display ?? 'Not set',
            'photo_url' => $this->photoUrl($employee),
        ];
    }

    protected function photoUrl(User $employee): ?string
    {
        if (! $employee->relationLoaded('employeeDocuments')) {
            return null;
        }

        $photo = $employee->employeeDocuments->firstWhere('document_type', 'photo');

        return $photo ? route('hr.employees.documents.view', [$employee->id, $photo->id]) : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeeDetail(User $employee): array
    {
        $profile = $employee->employeeProfile;

        return [
            ...$this->employeeSummary($employee),
            'profile' => $profile ? [
                'employment_status' => $profile->employment_status,
                'department' => $profile->department,
                'designation' => $profile->designation,
                'employment_type' => $profile->employment_type,
                'work_location' => $profile->work_location,
                'phone' => $profile->phone,
                'date_of_birth' => $profile->date_of_birth?->toDateString(),
                'nationality' => $profile->nationality,
                'address' => $profile->address,
                'skills' => $profile->skills,
                'experience_summary' => $profile->experience_summary,
                'religion' => $profile->religion,
                'blood_group' => $profile->blood_group,
                'marital_status' => $profile->marital_status,
                'qualification' => $profile->qualification,
                'joining_date' => $profile->joining_date?->toDateString(),
                'shift_id' => optional($employee->shiftAssignments()->where('is_current', true)->first())->shift_id,
                'weekly_off_policy_id' => optional($employee->weeklyOffAssignments()->where('is_current', true)->first())->weekly_off_policy_id,
                'current_shift_assignment_id' => optional($employee->shiftAssignments()->where('is_current', true)->first())->id,
                'current_weekly_off_assignment_id' => optional($employee->weeklyOffAssignments()->where('is_current', true)->first())->id,
                'current_reporting_manager_assignment_id' => optional($employee->reportingManagerAssignments()->where('is_current', true)->first())->id,
                'current_shift_name' => optional($employee->shiftAssignments()->where('is_current', true)->first())->shift?->shift_name,
                'current_weekly_off_policy_name' => optional($employee->weeklyOffAssignments()->where('is_current', true)->first())->weeklyOffPolicy?->policy_name,
                'current_shift_details' => (function () use ($employee) {
                    $assignment = $employee->shiftAssignments()->where('is_current', true)->first();
                    if (! $assignment || ! $assignment->shift) {
                        return null;
                    }

                    return [
                        'shift_name' => $assignment->shift->shift_name,
                        'shift_code' => $assignment->shift->shift_code,
                        'start_time' => $assignment->shift->start_time,
                        'end_time' => $assignment->shift->end_time,
                        'working_hours' => $assignment->shift->working_hours,
                        'grace_time' => $assignment->shift->grace_time,
                        'is_flexible' => $assignment->shift->is_flexible,
                    ];
                })(),
                'current_weekly_off_details' => (function () use ($employee) {
                    $assignment = $employee->weeklyOffAssignments()->where('is_current', true)->first();
                    if (! $assignment || ! $assignment->weeklyOffPolicy) {
                        return null;
                    }

                    return [
                        'policy_name' => $assignment->weeklyOffPolicy->policy_name,
                        'policy_code' => $assignment->weeklyOffPolicy->policy_code,
                        'description' => $assignment->weeklyOffPolicy->description,
                        'days' => $assignment->weeklyOffPolicy->days->map(fn ($d) => $d->day_of_week)->all(),
                    ];
                })(),
                'current_reporting_manager_details' => (function () use ($employee) {
                    $assignment = $employee->reportingManagerAssignments()->where('is_current', true)->first();
                    if (! $assignment || ! $assignment->manager) {
                        return null;
                    }

                    return [
                        'manager_id' => $assignment->manager->id,
                        'manager_name' => $assignment->manager->name,
                        'manager_employee_id' => $assignment->manager->employee_id,
                        'manager_email' => $assignment->manager->email,
                        'effective_from' => $assignment->effective_from,
                        'effective_to' => $assignment->effective_to,
                        'assignment_type' => $assignment->assignment_type,
                    ];
                })(),
                'probation_starts_on' => $profile->probation_starts_on?->toDateString(),
                'probation_ends_on' => $profile->probation_ends_on?->toDateString(),
                'probation_status' => $profile->probation_status,
                'confirmation_date' => $profile->confirmation_date?->toDateString(),
                'leave_policy_name' => $profile->leave_policy_name,
                'annual_leave_days' => $profile->annual_leave_days,
                'sick_leave_days' => $profile->sick_leave_days,
                'casual_leave_days' => $profile->casual_leave_days,
                'carry_forward_leave_days' => $profile->carry_forward_leave_days,
                'salary_amount' => $profile->salary_amount,
                'salary_currency' => $profile->salary_currency,
                'pay_frequency' => $profile->pay_frequency,
                'bank_name' => $profile->bank_name,
                'bank_account_number' => $profile->bank_account_number,
                'tax_identifier' => $profile->tax_identifier,
                'emergency_contact_name' => $profile->emergency_contact_name,
                'emergency_contact_phone' => $profile->emergency_contact_phone,
                'termination_date' => $profile->termination_date?->toDateString(),
                'termination_type' => $profile->termination_type,
                'termination_reason' => $profile->termination_reason,
                'last_rejoined_on' => $profile->last_rejoined_on?->toDateString(),
                'notes' => $profile->notes,
            ] : $this->emptyProfile(),
            'master_data_tags' => $employee->masterDataItems
                ->sortBy(fn ($item) => MasterDataItem::categories()[$item->category] ?? $item->category)
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'category' => $item->category,
                    'category_label' => MasterDataItem::categories()[$item->category] ?? $item->category,
                    'name' => $item->name,
                ])
                ->all(),
            'master_data_ids' => $this->employeeMasterData->selectedIdsForUser($employee),
            'documents' => (Schema::hasTable('employee_documents') ? $employee->employeeDocuments : collect())
                ->sortByDesc('created_at')
                ->values()
                ->map(fn (EmployeeDocument $document) => [
                    'id' => $document->id,
                    'type' => $document->document_type,
                    'label' => $document->label,
                    'filename' => $document->original_filename,
                    'expiry_date' => $document->expiry_date?->toDateString(),
                    'remarks' => $document->remarks,
                    'mime_type' => $document->mime_type,
                    'download_url' => route('hr.employees.documents.download', [$employee->id, $document->id]),
                    'view_url' => route('hr.employees.documents.view', [$employee->id, $document->id]),
                ])
                ->all(),
            'transfer_department_options' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
                ->values()
                ->all(),
            'promote_designation_options' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($item) => ['id' => $item->id, 'label' => $item->name])
                ->values()
                ->all(),
        ];
    }

    protected function storeDocuments(User $employee, Request $request): void
    {
        $documents = [
            'photo' => 'document_photo',
            'cv' => 'document_cv',
            'nid' => 'document_nid',
            'passport' => 'document_passport',
            'certificates' => 'document_certificates',
            'appointment_letter' => 'document_appointment_letter',
            'joining_letter' => 'document_joining_letter',
        ];
        $disk = config('filesystems.document_analyses_disk', 'local');

        foreach ($documents as $type => $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $path = $file->store('employee-documents/'.$employee->id, $disk);

            $employee->employeeDocuments()->create([
                'document_type' => $type,
                'file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'expiry_date' => $request->date("document_{$type}_expiry_date"),
                'remarks' => $request->input("document_{$type}_remarks"),
            ]);
        }
    }

    protected function loadDocumentsIfAvailable(User $employee): void
    {
        if (Schema::hasTable('employee_documents')) {
            $employee->load('employeeDocuments');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function profilePayload(Request $request): array
    {
        $masterDataValues = $this->employeeMasterData->resolveProfileValues($request->all());

        return [
            'employment_status' => $request->string('employment_status')->toString(),
            'department' => $masterDataValues['department'],
            'designation' => $masterDataValues['designation'],
            'employment_type' => $masterDataValues['employment_type'],
            'work_location' => $masterDataValues['work_location'],
            'phone' => $request->input('phone'),
            'date_of_birth' => $request->date('date_of_birth'),
            'nationality' => $request->input('nationality'),
            'address' => $request->input('address'),
            'skills' => $request->input('skills'),
            'experience_summary' => $request->input('experience_summary'),
            'religion' => $masterDataValues['religion'],
            'blood_group' => $masterDataValues['blood_group'],
            'marital_status' => $masterDataValues['marital_status'],
            'qualification' => $masterDataValues['qualification'],
            'joining_date' => $request->date('joining_date'),
            'probation_starts_on' => $request->date('probation_starts_on'),
            'probation_ends_on' => $request->date('probation_ends_on'),
            'probation_status' => $request->string('probation_status')->toString(),
            'confirmation_date' => $request->date('confirmation_date'),
            'leave_policy_name' => $request->input('leave_policy_name'),
            'annual_leave_days' => $request->integer('annual_leave_days'),
            'sick_leave_days' => $request->integer('sick_leave_days'),
            'casual_leave_days' => $request->integer('casual_leave_days'),
            'carry_forward_leave_days' => $request->integer('carry_forward_leave_days'),
            'salary_amount' => $request->input('salary_amount') !== null && $request->input('salary_amount') !== '' ? $request->input('salary_amount') : null,
            'salary_currency' => strtoupper($request->string('salary_currency')->toString()),
            'pay_frequency' => $masterDataValues['pay_frequency'] ?? $request->string('pay_frequency')->toString(),
            'bank_name' => $masterDataValues['bank_name'],
            'bank_account_number' => $request->input('bank_account_number'),
            'tax_identifier' => $request->input('tax_identifier'),
            'emergency_contact_name' => $request->input('emergency_contact_name'),
            'emergency_contact_phone' => $request->input('emergency_contact_phone'),
            'notes' => $request->input('notes'),
        ];
    }

    protected function recordSetupEvents(User $employee, Request $request): void
    {
        $this->recordEvent($employee, EmployeeLifecycleEvent::TYPE_PROFILE, 'Employee profile updated', now(), [
            'department' => $request->input('department'),
            'designation' => $request->input('designation'),
            'employment_status' => $request->input('employment_status'),
        ]);

        if ($request->filled('probation_starts_on') || $request->filled('probation_ends_on')) {
            $this->recordEvent($employee, EmployeeLifecycleEvent::TYPE_PROBATION, 'Probation setup updated', $request->date('probation_starts_on'), [
                'probation_starts_on' => $request->input('probation_starts_on'),
                'probation_ends_on' => $request->input('probation_ends_on'),
                'probation_status' => $request->input('probation_status'),
            ]);
        }

        if ($request->filled('leave_policy_name')) {
            $this->recordEvent($employee, EmployeeLifecycleEvent::TYPE_LEAVE, 'Leave setup updated', now(), [
                'leave_policy_name' => $request->input('leave_policy_name'),
                'annual_leave_days' => $request->input('annual_leave_days'),
                'sick_leave_days' => $request->input('sick_leave_days'),
                'casual_leave_days' => $request->input('casual_leave_days'),
                'carry_forward_leave_days' => $request->input('carry_forward_leave_days'),
            ]);
        }

        if ($request->filled('salary_amount')) {
            $this->recordEvent($employee, EmployeeLifecycleEvent::TYPE_SALARY, 'Salary setup updated', now(), [
                'salary_amount' => $request->input('salary_amount'),
                'salary_currency' => $request->input('salary_currency'),
                'pay_frequency' => $request->input('pay_frequency'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function recordEvent(User $employee, string $type, string $title, mixed $effectiveOn = null, array $payload = [], ?string $notes = null): void
    {
        $employee->employeeLifecycleEvents()->create([
            'created_by' => auth()->id(),
            'type' => $type,
            'effective_on' => $effectiveOn,
            'title' => $title,
            'notes' => $notes,
            'payload' => $payload,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function stats(): array
    {
        return [
            'total' => EmployeeProfile::count(),
            'onboarding' => EmployeeProfile::where('employment_status', EmployeeProfile::STATUS_ONBOARDING)->count(),
            'probation' => EmployeeProfile::where('employment_status', EmployeeProfile::STATUS_PROBATION)->count(),
            'active' => EmployeeProfile::whereIn('employment_status', [
                EmployeeProfile::STATUS_ACTIVE,
                EmployeeProfile::STATUS_REJOINED,
            ])->count(),
            'separated' => EmployeeProfile::whereIn('employment_status', [
                EmployeeProfile::STATUS_LEFT,
                EmployeeProfile::STATUS_TERMINATED,
            ])->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function options(): array
    {
        return [
            'employmentStatuses' => [
                EmployeeProfile::STATUS_ONBOARDING,
                EmployeeProfile::STATUS_PROBATION,
                EmployeeProfile::STATUS_ACTIVE,
                EmployeeProfile::STATUS_LEFT,
                EmployeeProfile::STATUS_TERMINATED,
                EmployeeProfile::STATUS_REJOINED,
            ],
            'probationStatuses' => [
                EmployeeProfile::PROBATION_PENDING,
                EmployeeProfile::PROBATION_CONFIRMED,
                EmployeeProfile::PROBATION_EXTENDED,
                EmployeeProfile::PROBATION_FAILED,
            ],
            'employmentTypes' => ['full_time', 'part_time', 'contract', 'intern', 'consultant'],
            'payFrequencies' => ['monthly', 'biweekly', 'weekly', 'hourly'],
            'departments' => EmployeeProfile::query()
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department')
                ->values()
                ->all(),
            'shifts' => Shift::query()
                ->where('status', true)
                ->orderBy('shift_name')
                ->get(['id', 'shift_name', 'shift_code', 'start_time', 'end_time', 'working_hours', 'is_flexible', 'grace_time'])
                ->map(fn ($shift) => [
                    'id' => $shift->id,
                    'label' => "{$shift->shift_name} ({$shift->shift_code})",
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'working_hours' => $shift->working_hours,
                    'is_flexible' => $shift->is_flexible,
                    'grace_time' => $shift->grace_time,
                ])
                ->values()
                ->all(),
            'weeklyOffPolicies' => WeeklyOffPolicy::query()
                ->where('status', true)
                ->orderBy('policy_name')
                ->with(['days'])
                ->get(['id', 'policy_name', 'policy_code', 'description'])
                ->map(fn ($policy) => [
                    'id' => $policy->id,
                    'label' => "{$policy->policy_name} ({$policy->policy_code})",
                    'description' => $policy->description,
                    'days' => $policy->days->map(fn ($d) => $d->day_of_week)->all(),
                ])
                ->values()
                ->all(),
            'managers' => User::query()
                ->whereHas('roles', fn ($roles) => $roles->where('name', User::ROLE_EMPLOYEE))
                ->orderBy('name')
                ->get(['id', 'employee_id', 'name'])
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'label' => "{$user->employee_id} - {$user->name}",
                ])
                ->values()
                ->all(),
            'masterData' => $this->employeeMasterData->formOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyProfile(): array
    {
        return [
            'employment_status' => EmployeeProfile::STATUS_ONBOARDING,
            'department' => '',
            'designation' => '',
            'employment_type' => 'full_time',
            'work_location' => '',
            'phone' => '',
            'date_of_birth' => '',
            'nationality' => '',
            'address' => '',
            'skills' => '',
            'experience_summary' => '',
            'religion' => '',
            'blood_group' => '',
            'marital_status' => '',
            'qualification' => '',
            'joining_date' => '',
            'shift_id' => '',
            'weekly_off_policy_id' => '',
            'manager_id' => '',
            'probation_starts_on' => '',
            'probation_ends_on' => '',
            'probation_status' => EmployeeProfile::PROBATION_PENDING,
            'confirmation_date' => '',
            'leave_policy_name' => '',
            'annual_leave_days' => 0,
            'sick_leave_days' => 0,
            'casual_leave_days' => 0,
            'carry_forward_leave_days' => 0,
            'salary_amount' => '',
            'salary_currency' => 'BDT',
            'pay_frequency' => 'monthly',
            'bank_name' => '',
            'bank_account_number' => '',
            'tax_identifier' => '',
            'emergency_contact_name' => '',
            'emergency_contact_phone' => '',
            'termination_date' => '',
            'termination_type' => '',
            'termination_reason' => '',
            'last_rejoined_on' => '',
            'notes' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, array{id: int, label: string}>
     */
    protected function masterDataSuggestions(array $data): array
    {
        $fields = [
            'religion' => MasterDataItem::CATEGORY_RELIGION,
            'blood_group' => MasterDataItem::CATEGORY_BLOOD_GROUP,
            'marital_status' => MasterDataItem::CATEGORY_MARITAL_STATUS,
            'qualification' => MasterDataItem::CATEGORY_QUALIFICATION,
            'department' => MasterDataItem::CATEGORY_DEPARTMENT,
            'designation' => MasterDataItem::CATEGORY_DESIGNATION,
            'employment_type' => MasterDataItem::CATEGORY_EMPLOYEE_TYPE,
        ];

        $suggestions = [];

        foreach ($fields as $field => $category) {
            if (! isset($data[$field])) {
                continue;
            }

            $needle = mb_strtolower(trim((string) $data[$field]));
            $item = MasterDataItem::query()
                ->where('category', $category)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->get(['id', 'name'])
                ->first(fn (MasterDataItem $candidate) => mb_strtolower($candidate->name) === $needle);

            if ($item) {
                $selectionField = match ($field) {
                    'employment_type' => 'employment_type_master_data_id',
                    default => $field.'_master_data_id',
                };
                $suggestions[$selectionField] = ['id' => $item->id, 'label' => $item->name];
            }
        }

        return $suggestions;
    }
}
