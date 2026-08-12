<?php

use App\Http\Controllers\Admin\DocAnalyzerController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR\EmployeeController as HREmployeeController;
use App\Http\Controllers\HR\MasterDataItemController as HRMasterDataItemController;

use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\ShiftController;
use App\Http\Controllers\HR\ShiftScheduleController;
use App\Http\Controllers\HR\ShiftSwapRequestController;

use App\Http\Controllers\HR\WeeklyOffPolicyController;
use App\Http\Controllers\HR\EmployeeWeeklyOffAssignmentController;
use App\Http\Controllers\HR\EmployeeShiftAssignmentController;
use App\Http\Controllers\HR\BulkAssignmentController;
use App\Http\Controllers\HR\Approval\ApprovalRequestController;
use App\Http\Controllers\HR\Approval\ApprovalWorkflowController;
use App\Http\Controllers\HR\EmployeeReportingManagerAssignmentController;
use App\Http\Controllers\HR\EmployeeTransferController;
use App\Http\Controllers\HR\EmploymentMovementController;
use App\Http\Controllers\HR\Leave\HolidayController;
use App\Http\Controllers\HR\Leave\LeaveLedgerController;
use App\Http\Controllers\HR\Leave\LeavePolicyAssignmentController;
use App\Http\Controllers\HR\Leave\LeavePolicyController;
use App\Http\Controllers\HR\Leave\LeavePolicyDetailController;
use App\Http\Controllers\HR\Leave\LeaveTypeController;
use App\Http\Controllers\HR\Leave\OpeningBalanceController;
use App\Http\Controllers\HR\Leave\LeaveApplicationController;
use App\Http\Controllers\HR\Leave\LeaveAttachmentController;
use App\Http\Controllers\HR\Leave\LeaveDelegateController;
use App\Http\Controllers\HR\Leave\LeaveBalanceController;
use App\Http\Controllers\HR\Leave\TeamLeaveCalendarController;
use App\Http\Controllers\HR\Leave\ManagerLeaveDashboardController;
use App\Http\Controllers\HR\Leave\HRLeaveDashboardController;
use App\Http\Controllers\HR\Leave\HRLeaveCalendarController;
use App\Http\Controllers\Employee\Leave\LeaveCalendarController;


use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route($request->user()->dashboardRoute());
    }

    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()->dashboardRoute());
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('users', AdminUserController::class)
            ->except('show')
            ->middleware('permission:manage users');
        Route::resource('roles', AdminRoleController::class)
            ->except('show')
            ->middleware('permission:manage roles');
        Route::resource('permissions', AdminPermissionController::class)
            ->except('show')
            ->middleware('permission:manage permissions');
        Route::get('/reports', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'admin',
                'title' => 'Executive Reports',
                'description' => 'Monitor workforce KPIs, department trends, and access-sensitive operational insights.',
                'highlights' => [
                    'Headcount movement across teams',
                    'Attendance irregularity signals',
                    'Leave approval turnaround trends',
                ],
            ]);
        })->middleware('permission:view reports')->name('reports');

        Route::middleware('permission:doc-analyzer.view-doc-analyzer')->group(function () {
            Route::get('/doc-analyzer', [DocAnalyzerController::class, 'index'])->name('doc-analyzer.index');
            Route::post('/doc-analyzer', [DocAnalyzerController::class, 'store'])->name('doc-analyzer.store');
            Route::get('/doc-analyzer/{documentAnalysis}', [DocAnalyzerController::class, 'show'])->name('doc-analyzer.show');
            Route::delete('/doc-analyzer/{documentAnalysis}', [DocAnalyzerController::class, 'destroy'])->name('doc-analyzer.destroy');
        });
    });

    Route::prefix('hr')->name('hr.')->middleware('role:hr')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hr'])->name('dashboard');
        Route::middleware('permission:manage employees')->group(function () {
            Route::post('/employees/cv-extract', [HREmployeeController::class, 'extractCv'])
                ->name('employees.cv-extract');
            Route::resource('employees', HREmployeeController::class)
                ->except('destroy')
                ->parameters(['employees' => 'employee']);
            Route::post('/employees/{employee}/terminate', [HREmployeeController::class, 'terminate'])
                ->name('employees.terminate');
            Route::post('/employees/{employee}/rejoin', [HREmployeeController::class, 'rejoin'])
                ->name('employees.rejoin');
            Route::post('/employees/{employee}/transfer', [HREmployeeController::class, 'transfer'])
                ->name('employees.transfer');
            Route::post('/employees/{employee}/promote', [HREmployeeController::class, 'promote'])
                ->name('employees.promote');
            Route::post('/employees/{employee}/increment', [HREmployeeController::class, 'increment'])
                ->name('employees.increment');
            Route::get('/employees/{employee}/documents/{document}/download', [HREmployeeController::class, 'downloadDocument'])
                ->name('employees.documents.download');
            Route::get('/employees/{employee}/documents/{document}/view', [HREmployeeController::class, 'viewDocument'])
                ->name('employees.documents.view');
        });
        Route::get('/master-data', [HRMasterDataItemController::class, 'index'])
            ->middleware('permission:master-data.view-master-data')
            ->name('master-data.index');
        Route::post('/master-data', [HRMasterDataItemController::class, 'store'])
            ->middleware('permission:master-data.create-master-data')
            ->name('master-data.store');
        Route::match(['put', 'patch'], '/master-data/{master_data_item}', [HRMasterDataItemController::class, 'update'])
            ->middleware('permission:master-data.edit-master-data')
            ->name('master-data.update');
        Route::delete('/master-data/{master_data_item}', [HRMasterDataItemController::class, 'destroy'])
            ->middleware('permission:master-data.delete-master-data')
            ->name('master-data.destroy');

        Route::middleware('permission:manage attendance')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

            Route::post('/shift-schedules', [ShiftScheduleController::class, 'store'])->name('shift-schedules.store');
            Route::patch('/shift-schedules/{shiftSchedule}', [ShiftScheduleController::class, 'update'])->name('shift-schedules.update');
            Route::delete('/shift-schedules/{shiftSchedule}', [ShiftScheduleController::class, 'destroy'])->name('shift-schedules.destroy');

            Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
            Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
            Route::patch('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
            Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

            Route::get('/shift-swaps', [ShiftSwapRequestController::class, 'index'])->name('shift-swaps.index');
            Route::post('/shift-swaps', [ShiftSwapRequestController::class, 'store'])->name('shift-swaps.store');
            Route::post('/shift-swaps/{swapRequest}/accept', [ShiftSwapRequestController::class, 'accept'])->name('shift-swaps.accept');
            Route::post('/shift-swaps/{swapRequest}/review', [ShiftSwapRequestController::class, 'review'])->name('shift-swaps.review');
            Route::delete('/shift-swaps/{swapRequest}', [ShiftSwapRequestController::class, 'cancel'])->name('shift-swaps.cancel');

            Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
            Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');


            Route::get('/shift-schedules', [ShiftScheduleController::class, 'index'])->middleware('permission:manage attendance')->name('shift-schedules.index');

            Route::get('/shift-swap-requests', function () {
                return Inertia::render('Modules/Show', [
                    'layout' => 'hr',
                    'title' => 'Shift Swap Requests',
                    'description' => 'Review and action employee shift swap requests before schedules are finalized.',
                    'highlights' => [
                        'Pending swap approvals',
                        'Requester and target shift details',
                        'Approval history and audit trail',
                    ],
                ]);
            })->middleware('permission:manage attendance')->name('shift-swap-requests.index');


            Route::resource(
                'weekly-off-policies',
                WeeklyOffPolicyController::class
            );

            Route::get(
                '/weekly-off-policies/{weeklyOffPolicy}/clone',
                [WeeklyOffPolicyController::class, 'clone']
            )->name('weekly-off-policies.clone');



            Route::controller(EmployeeWeeklyOffAssignmentController::class)
                ->prefix('weekly-off-assignments')
                ->name('weekly-off-assignments.')
                ->group(function () {

                    /*
                |--------------------------------------------------------------------------
                | List
                |--------------------------------------------------------------------------
                */

                    Route::get('/', 'index')
                        ->name('index');

                    /*
                |--------------------------------------------------------------------------
                | Initial Assignment
                |--------------------------------------------------------------------------
                */

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    /*
                |--------------------------------------------------------------------------
                | Details
                |--------------------------------------------------------------------------
                */

                    Route::get('/{assignment}', 'show')
                        ->name('show');

                    /*
                |--------------------------------------------------------------------------
                | Change Assignment
                |--------------------------------------------------------------------------
                */

                    Route::get('/{assignment}/change', 'change')
                        ->name('change');

                    Route::post('/{assignment}/change', 'storeChange')
                        ->name('store-change');

                    /*
                |--------------------------------------------------------------------------
                | Assignment History
                |--------------------------------------------------------------------------
                */

                    Route::get('/{assignment}/history', 'history')
                        ->name('history');

                    /*
                |--------------------------------------------------------------------------
                | Delete (Only if never used)
                |--------------------------------------------------------------------------
                */

                    Route::delete('/{assignment}', 'destroy')
                        ->name('destroy');
                });


            // Shift Assignments
            Route::controller(EmployeeShiftAssignmentController::class)
                ->prefix('shift-assignments')
                ->name('shift-assignments.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{assignment}/edit', 'edit')
                        ->name('edit');

                    Route::put('/{assignment}', 'update')
                        ->name('update');

                    Route::delete('/{assignment}', 'destroy')
                        ->name('destroy');

                    Route::get('/{assignment}/change', 'change')
                        ->name('change');

                    Route::post('/{assignment}/change', 'storeChange')
                        ->name('store-change');

                    Route::get('/{assignment}/history', 'history')
                        ->name('history');
                });
            // =========

            Route::controller(BulkAssignmentController::class)
                ->prefix('bulk-assignments')
                ->name('bulk-assignments.')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                });

            Route::controller(EmploymentMovementController::class)
                ->prefix('employment-movements')
                ->name('employment-movements.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{movement}', 'show')
                        ->name('show');

                    Route::get('/{movement}/edit', 'edit')
                        ->name('edit');

                    Route::put('/{movement}', 'update')
                        ->name('update');

                    Route::delete('/{movement}', 'destroy')
                        ->name('destroy');

                    Route::get('/{employee}/history', 'history')
                        ->name('history');
                });

            Route::controller(EmployeeTransferController::class)
                ->prefix('transfers')
                ->name('transfers.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{transfer}', 'show')
                        ->name('show');

                    Route::get('/{transfer}/edit', 'edit')
                        ->name('edit');

                    Route::put('/{transfer}', 'update')
                        ->name('update');

                    Route::delete('/{transfer}', 'destroy')
                        ->name('destroy');

                    Route::post('/{transfer}/approve', 'approve')
                        ->name('approve');

                    Route::post('/{transfer}/reject', 'reject')
                        ->name('reject');

                    Route::get('/{employee}/history', 'history')
                        ->name('history');
                });

            Route::controller(EmployeeReportingManagerAssignmentController::class)
                ->prefix('reporting-manager-assignments')
                ->name('reporting-manager-assignments.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{assignment}/edit', 'edit')
                        ->name('edit');

                    Route::put('/{assignment}', 'update')
                        ->name('update');

                    Route::delete('/{assignment}', 'destroy')
                        ->name('destroy');

                    Route::get('/{assignment}/change', 'change')
                        ->name('change');

                    Route::post('/{assignment}/change', 'storeChange')
                        ->name('store-change');

                    Route::get('/{assignment}/history', 'history')
                        ->name('history');
                });

            Route::controller(ApprovalWorkflowController::class)
                ->prefix('approval/workflows')
                ->name('approval.workflows.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/create', 'create')
                        ->name('create');

                    Route::post('/', 'store')
                        ->name('store');

                    Route::get('/{workflow}', 'show')
                        ->name('show');

                    Route::get('/{workflow}/edit', 'edit')
                        ->name('edit');

                    Route::put('/{workflow}', 'update')
                        ->name('update');

                    Route::delete('/{workflow}', 'destroy')
                        ->name('destroy');
                });

            Route::controller(ApprovalRequestController::class)
                ->prefix('approval/requests')
                ->name('approval.requests.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index');

                    Route::get('/pending', 'pending')
                        ->name('pending');

                    Route::get('/history', 'history')
                        ->name('history');

                    Route::get('/{request}', 'show')
                        ->name('show');
                });

            Route::controller(LeaveTypeController::class)
                ->prefix('leave/types')
                ->name('leave.types.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage leave types');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage leave types');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage leave types');

                    Route::get('/{leave_type}', 'show')
                        ->name('show')
                        ->middleware('permission:manage leave types');

                    Route::get('/{leave_type}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage leave types');

                    Route::put('/{leave_type}', 'update')
                        ->name('update')
                        ->middleware('permission:manage leave types');

                    Route::delete('/{leave_type}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage leave types');
                });

            Route::controller(LeavePolicyController::class)
                ->prefix('leave/policies')
                ->name('leave.policies.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage leave policies');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage leave policies');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage leave policies');

                    Route::get('/{leave_policy}', 'show')
                        ->name('show')
                        ->middleware('permission:leave.view-policy');

                    Route::get('/{leave_policy}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage leave policies');

                    Route::put('/{leave_policy}', 'update')
                        ->name('update')
                        ->middleware('permission:manage leave policies');

                    Route::delete('/{leave_policy}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage leave policies');
                });

            Route::controller(LeavePolicyDetailController::class)
                ->prefix('leave/policies/{leave_policy}/details')
                ->name('leave.policies.details.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage leave policies');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage leave policies');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage leave policies');

                    Route::get('/{detail}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage leave policies');

                    Route::put('/{detail}', 'update')
                        ->name('update')
                        ->middleware('permission:manage leave policies');

                    Route::delete('/{detail}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage leave policies');
                });

            Route::controller(LeavePolicyAssignmentController::class)
                ->prefix('leave/assignments')
                ->name('leave.assignments.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage leave assignments');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage leave assignments');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage leave assignments');

                    Route::get('/{assignment}', 'show')
                        ->name('show')
                        ->middleware('permission:manage leave assignments');

                    Route::get('/{assignment}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage leave assignments');

                    Route::put('/{assignment}', 'update')
                        ->name('update')
                        ->middleware('permission:manage leave assignments');

                    Route::delete('/{assignment}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage leave assignments');
                });

            Route::controller(HolidayController::class)
                ->prefix('leave/holidays')
                ->name('leave.holidays.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage holidays');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage holidays');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage holidays');

                    Route::get('/{holiday}', 'show')
                        ->name('show')
                        ->middleware('permission:manage holidays');

                    Route::get('/{holiday}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage holidays');

                    Route::put('/{holiday}', 'update')
                        ->name('update')
                        ->middleware('permission:manage holidays');

                    Route::delete('/{holiday}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage holidays');
                });

            Route::controller(OpeningBalanceController::class)
                ->prefix('leave/opening-balances')
                ->name('leave.opening-balances.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:manage leave balances');

                    Route::get('/import', 'import')
                        ->name('import')
                        ->middleware('permission:manage leave balances');

                    Route::post('/import/preview', 'preview')
                        ->name('import.preview')
                        ->middleware('permission:manage leave balances');

                    Route::post('/import/commit', 'commit')
                        ->name('import.commit')
                        ->middleware('permission:manage leave balances');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:manage leave balances');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:manage leave balances');

                    Route::get('/{opening_balance}', 'show')
                        ->name('show')
                        ->middleware('permission:manage leave balances');

                    Route::get('/{opening_balance}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:manage leave balances');

                    Route::put('/{opening_balance}', 'update')
                        ->name('update')
                        ->middleware('permission:manage leave balances');

                    Route::delete('/{opening_balance}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:manage leave balances');

                    Route::post('/sync', 'sync')
                        ->name('sync')
                        ->middleware('permission:manage leave balances');
                });

            Route::controller(LeaveLedgerController::class)
                ->prefix('leave/ledgers')
                ->name('leave.ledgers.')
                ->group(function () {

                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:view leave ledger');

                    Route::get('/summary', 'summary')
                        ->name('summary')
                        ->middleware('permission:view leave ledger');

                    Route::get('/{ledger}', 'show')
                        ->name('show')
                        ->middleware('permission:view leave ledger');
                });

            Route::controller(LeaveBalanceController::class)
                ->prefix('leave/balances')
                ->name('leave.balances.')
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:view leave balances');

                    Route::get('/employees/{employee}', 'employeeBalances')
                        ->name('employee')
                        ->middleware('permission:view leave balances');

                    Route::get('/employees/{employee}/leave-types/{leaveType}', 'show')
                        ->name('show')
                        ->middleware('permission:view leave balances');

                    Route::get('/employees/{employee}/leave-types/{leaveType}/ledger', 'ledger')
                        ->name('ledger')
                        ->middleware('permission:view leave ledger');
                });

            Route::controller(LeaveApplicationController::class)
                ->prefix('leave/applications')
                ->name('leave.applications.')
                ->group(function () {
                    Route::get('/', 'index')
                        ->name('index')
                        ->middleware('permission:leave.manage-applications');

                    Route::get('/create', 'create')
                        ->name('create')
                        ->middleware('permission:apply for leave');

                    Route::post('/', 'store')
                        ->name('store')
                        ->middleware('permission:apply for leave');

                    Route::get('/{application}', 'show')
                        ->name('show')
                        ->middleware('permission:leave.manage-applications');

                    Route::get('/{application}/edit', 'edit')
                        ->name('edit')
                        ->middleware('permission:leave.manage-applications');

                    Route::put('/{application}', 'update')
                        ->name('update')
                        ->middleware('permission:leave.manage-applications');

                    Route::post('/{application}/submit', 'submit')
                        ->name('submit')
                        ->middleware('permission:submit leave application');

                    Route::post('/{application}/cancel', 'cancel')
                        ->name('cancel')
                        ->middleware('permission:cancel leave application');

                    Route::post('/{application}/withdraw', 'withdraw')
                        ->name('withdraw')
                        ->middleware('permission:withdraw leave application');

                    Route::delete('/{application}', 'destroy')
                        ->name('destroy')
                        ->middleware('permission:delete leave application');
                });

            Route::post('leave/applications/{application}/attachments', [LeaveAttachmentController::class, 'store'])
                ->name('leave.applications.attachments.store')
                ->middleware('permission:upload leave attachment');

            Route::delete('leave/applications/{application}/attachments/{attachment}', [LeaveAttachmentController::class, 'destroy'])
                ->name('leave.applications.attachments.destroy')
                ->middleware('permission:delete leave attachment');

            Route::get('leave/applications/{application}/attachments/{attachment}/download', [LeaveAttachmentController::class, 'download'])
                ->name('leave.applications.attachments.download')
                ->middleware('permission:download leave attachment');

            Route::post('/{application}/delegate/accept', [LeaveDelegateController::class, 'accept'])
                ->name('leave.applications.delegate.accept')
                ->middleware('permission:accept leave delegation');

            Route::post('/{application}/delegate/decline', [LeaveDelegateController::class, 'decline'])
                ->name('leave.applications.delegate.decline')
                ->middleware('permission:decline leave delegation');
            // =========
        });

        Route::get('/payroll', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'hr',
                'title' => 'Payroll Operations',
                'description' => 'Run payroll confidently with the right access boundaries and operational visibility.',
                'highlights' => [
                    'Validate payroll batches',
                    'Review compensation change windows',
                    'Flag missing attendance inputs',
                ],
            ]);
        })->middleware('permission:manage payroll')->name('payroll');

        Route::get('/leave-requests', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'hr',
                'title' => 'Leave Approvals',
                'description' => 'Review employee leave requests, balance trends, and escalations in one place.',
                'highlights' => [
                    'Pending approvals queue',
                    'Upcoming team absences',
                    'Policy exception handling',
                ],
            ]);
        })->middleware('permission:manage leave requests')->name('leave');

        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('attachments', [\App\Http\Controllers\HR\Leave\LeaveAttachmentController::class, 'hrIndex'])
                ->name('attachments.index')
                ->middleware('permission:leave.view-all-applications');

            Route::post('attachments/{attachment}/verify', [\App\Http\Controllers\HR\Leave\LeaveAttachmentController::class, 'verify'])
                ->name('attachments.verify')
                ->middleware('permission:leave.verify-attachment');

            Route::post('attachments/{attachment}/reject', [\App\Http\Controllers\HR\Leave\LeaveAttachmentController::class, 'reject'])
                ->name('attachments.reject')
                ->middleware('permission:leave.reject-attachment');

            Route::middleware('permission:leave.view-reports')->group(function () {

                Route::controller(ManagerLeaveDashboardController::class)
                    ->prefix('manager-dashboard')
                    ->name('manager-dashboard.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                    });

                Route::controller(HRLeaveCalendarController::class)
                    ->prefix('calendar')
                    ->name('calendar.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                        Route::get('/day-details', 'dayDetails')->name('day-details');
                    });

                Route::controller(HRLeaveDashboardController::class)
                    ->prefix('dashboard')
                    ->name('dashboard.')
                    ->group(function () {
                        Route::get('/', 'index')->name('index');
                    });
            });
        });
    });



    Route::prefix('employee')->name('employee.')->middleware('role:employee')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'employee'])->name('dashboard');
        Route::get('/attendance', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'employee',
                'title' => 'Attendance Hub',
                'description' => 'Track your attendance, correct exceptions, and stay aligned with payroll cutoffs.',
                'highlights' => [
                    'Today\'s check-in status',
                    'Pending attendance corrections',
                    'Monthly punctuality snapshot',
                ],
            ]);
        })->middleware('permission:manage attendance')->name('attendance');
        Route::get('/leave', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'employee',
                'title' => 'Leave Center',
                'description' => 'Submit leave requests, monitor approvals, and check your remaining balance.',
                'highlights' => [
                    'Available leave balance',
                    'Recent approvals and comments',
                    'Upcoming scheduled time off',
                ],
            ]);
        })->middleware('permission:manage leave requests')->name('leave');

        Route::controller(LeaveApplicationController::class)
            ->prefix('leave/applications')
            ->name('leave.applications.')
            ->group(function () {
                Route::get('/', 'employeeIndex')
                    ->name('index')
                    ->middleware('permission:leave.view-own-applications');

                Route::get('/create', 'create')
                    ->name('create')
                    ->middleware('permission:leave.apply');

                Route::post('/', 'store')
                    ->name('store')
                    ->middleware('permission:leave.apply');

                Route::get('/{application}', 'show')
                    ->name('show')
                    ->middleware('permission:leave.view-own-applications');

                Route::get('/{application}/edit', 'edit')
                    ->name('edit')
                    ->middleware('permission:leave.edit-own-application');

                Route::put('/{application}', 'update')
                    ->name('update')
                    ->middleware('permission:leave.edit-own-application');

                Route::post('/{application}/submit', 'submit')
                    ->name('submit')
                    ->middleware('permission:leave.submit-application');

                Route::post('/{application}/cancel', 'cancel')
                    ->name('cancel')
                    ->middleware('permission:leave.cancel-own-application');

                Route::post('/{application}/withdraw', 'withdraw')
                    ->name('withdraw')
                    ->middleware('permission:leave.withdraw-application');
            });

        Route::post('leave/applications/{application}/attachments', [LeaveAttachmentController::class, 'store'])
            ->name('employee.leave.applications.attachments.store')
            ->middleware('permission:leave.upload-attachment');

        Route::delete('leave/applications/{application}/attachments/{attachment}', [LeaveAttachmentController::class, 'destroy'])
            ->name('employee.leave.applications.attachments.destroy')
            ->middleware('permission:leave.delete-attachment');

        Route::get('leave/applications/{application}/attachments/{attachment}/download', [LeaveAttachmentController::class, 'download'])
            ->name('employee.leave.applications.attachments.download')
            ->middleware('permission:leave.download-attachment');

        Route::get('leave/delegations', [LeaveDelegateController::class, 'index'])
            ->name('employee.leave.delegations.index')
            ->middleware('permission:leave.view-delegations');

        Route::get('leave/delegations/{application}', [LeaveDelegateController::class, 'show'])
            ->name('employee.leave.delegations.show')
            ->middleware('permission:leave.view-delegations');

        Route::post('leave/delegations/{application}/accept', [LeaveDelegateController::class, 'accept'])
            ->name('employee.leave.delegations.accept')
            ->middleware('permission:leave.accept-delegation');

        Route::post('leave/delegations/{application}/decline', [LeaveDelegateController::class, 'decline'])
            ->name('employee.leave.delegations.decline')
            ->middleware('permission:leave.decline-delegation');

        Route::controller(LeaveCalendarController::class)
            ->prefix('leave/calendar')
            ->name('leave.calendar.')
            ->group(function () {
                Route::get('/', 'index')->name('index')->middleware('permission:leave.view-own-applications');
            });

        Route::controller(TeamLeaveCalendarController::class)
            ->prefix('leave/team-calendar')
            ->name('leave.team-calendar.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/summary', 'summary')->name('summary');
            });
    });
});



require __DIR__ . '/auth.php';
