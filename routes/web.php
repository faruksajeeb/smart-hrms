<?php

use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
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
    });

    Route::prefix('hr')->name('hr.')->middleware('role:hr')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'hr'])->name('dashboard');
        Route::get('/employees', function () {
            return Inertia::render('Modules/Show', [
                'layout' => 'hr',
                'title' => 'Employee Management',
                'description' => 'Keep employment records current, support onboarding, and maintain organizational structure.',
                'highlights' => [
                    'Track employee profile updates',
                    'Review department assignments',
                    'Prepare records for audits',
                ],
            ]);
        })->middleware('permission:manage employees')->name('employees');
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
