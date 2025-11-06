<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Student and Supervisor Registration Routes
Route::get('student/register', [StudentController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('student.register');
Route::post('student/register', [StudentController::class, 'register'])
    ->middleware('guest');
Route::get('supervisor/register', [SupervisorController::class, 'showRegisterForm'])
    ->middleware('guest')
    ->name('supervisor.register');
Route::post('supervisor/register', [SupervisorController::class, 'register'])
    ->middleware('guest');

// Dashboard Routes
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('student/dashboard', 'student-dashboard')
    ->middleware(['auth', 'verified', 'role:student'])
    ->name('student.dashboard');

Route::view('supervisor/dashboard', 'supervisor-dashboard')
    ->middleware(['auth', 'verified', 'role:supervisor'])
    ->name('supervisor.dashboard');

Route::view('superadmin/dashboard', 'superadmin-dashboard')
    ->middleware(['auth', 'verified', 'role:superadmin'])
    ->name('superadmin.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// SIWES routes for students
Route::middleware(['auth', 'role:student'])->prefix('siwes')->name('siwes.')->group(function () {
    Volt::route('ppa-setup', 'siwes.ppa-setup')->name('ppa-setup');
    Volt::route('dashboard', 'siwes.dashboard')->name('dashboard');
    Volt::route('log-activity', 'siwes.log-activity')->name('log-activity');
    Volt::route('weekly-summary', 'siwes.weekly-summary')->name('weekly-summary');
    Volt::route('activity-history', 'siwes.activity-history')->name('activity-history');
    Volt::route('final-report', 'siwes.final-report')->name('final-report');
    
    // Route to view submitted final report
    Route::get('view-report', function () {
        $user = auth()->user();
        if (!$user->final_report_path || !Storage::disk('public')->exists($user->final_report_path)) {
            abort(404, 'Report not found');
        }
        return response()->file(Storage::disk('public')->path($user->final_report_path));
    })->name('view-report');
});

// SIWES routes for supervisors
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Volt::route('siwes-approvals', 'supervisor.siwes-approvals')->name('siwes-approvals');
    Volt::route('students', 'supervisor.students')->name('students');
    Volt::route('student-activities', 'supervisor.student-activities')->name('student-activities');
    Volt::route('student-reports', 'supervisor.student-reports')->name('student-reports');
    
    Route::get('view-student-report/{userId}', function ($userId) {
        $student = \App\Models\User::findOrFail($userId);
        if ($student->supervisor_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this student report');
        }
        if (!$student->final_report_path || !Storage::disk('public')->exists($student->final_report_path)) {
            abort(404, 'Report not found');
        }
        return response()->file(Storage::disk('public')->path($student->final_report_path));
    })->name('view-student-report');
});

// Admin routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Volt::route('roles', 'admin.role-management')->name('roles');
    Volt::route('accounts', 'admin.account-management')->name('accounts');
    Volt::route('supervisors', 'admin.supervisor-management')->name('supervisors');
    Volt::route('departments', 'admin.department-management')->name('departments');
    Volt::route('departments/{id}', 'admin.department-detail')->name('department.detail');
    Volt::route('student-reports', 'admin.student-reports')->name('student-reports');
    
    Route::get('view-student-report/{userId}', function ($userId) {
        $student = \App\Models\User::findOrFail($userId);
        if (!$student->final_report_path || !Storage::disk('public')->exists($student->final_report_path)) {
            abort(404, 'Report not found');
        }
        return response()->file(Storage::disk('public')->path($student->final_report_path));
    })->name('view-student-report');
});
