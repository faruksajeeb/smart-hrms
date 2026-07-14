<?php

use App\Http\Controllers\Admin\DocAnalyzerController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR\EmployeeController as HREmployeeController;
use App\Http\Controllers\HR\MasterDataItemController as HRMasterDataItemController;
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
            Route::get('/employees/{employee}/documents/{document}/download', [HREmployeeController::class, 'downloadDocument'])
                ->name('employees.documents.download');
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
    });
});

require __DIR__.'/auth.php';
