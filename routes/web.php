<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

// Company Registration routes
Route::get('/register/company', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'showRegistrationForm'])->name('company.register.form');
Route::post('/register/company', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'register'])->name('company.register');
Route::post('/check-company-email', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'checkCompanyEmail'])->name('company.check-email');
Route::post('/check-owner-email', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'checkOwnerEmail'])->name('owner.check-email');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees-data', [EmployeeController::class, 'getEmployees'])->name('employees.data');

    // Payroll
    Route::resource('payroll', PayrollController::class);
    Route::get('/payroll-data', [PayrollController::class, 'getData'])->name('payroll.data');

    // Attendance
    Route::resource('attendance', AttendanceController::class);
    Route::get('/attendance-data', [AttendanceController::class, 'getData'])->name('attendance.data');

    // Reports (placeholder routes)
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    // Settings (placeholder routes)
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');
});
