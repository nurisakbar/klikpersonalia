<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Mobile API Routes
Route::prefix('mobile')->group(function () {
    // Authentication
    Route::post('/login', [AuthController::class, 'mobileLogin']);
    Route::post('/logout', [AuthController::class, 'mobileLogout'])->middleware('auth:sanctum');
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::get('/dashboard', [MobileController::class, 'dashboard']);
        
        // Attendance
        Route::post('/attendance/check-in-out', [MobileController::class, 'checkInOut']);
        Route::get('/attendance/history', [MobileController::class, 'attendanceHistory']);
        
        // Payroll
        Route::get('/payslip/{id}', [MobileController::class, 'getPayslip']);
        Route::get('/payslips', [MobileController::class, 'getPayslips']);
        
        // Leave Management
        Route::post('/leave/request', [MobileController::class, 'submitLeaveRequest']);
        Route::get('/leave/history', [MobileController::class, 'getLeaveHistory']);
        Route::get('/leave/balance', [MobileController::class, 'getLeaveBalance']);
        
        // Overtime Management
        Route::post('/overtime/request', [MobileController::class, 'submitOvertimeRequest']);
        Route::get('/overtime/history', [MobileController::class, 'getOvertimeHistory']);
        
        // Profile
        Route::get('/profile', [MobileController::class, 'getProfile']);
        Route::put('/profile', [MobileController::class, 'updateProfile']);
        
        // Notifications
        Route::get('/notifications', [MobileController::class, 'getNotifications']);
        Route::put('/notifications/{id}/read', [MobileController::class, 'markNotificationRead']);
    });
}); 