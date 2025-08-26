<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\AttendanceCalendarController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\BenefitController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\BpjsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\SalaryTransferController;
use App\Http\Controllers\ExternalIntegrationController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;

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

// Landing page route
Route::get('/', function () {
    return redirect()->route('login');
})->name('landing');

// Landing page route (accessible via /landing)
Route::get('/landing', function () {
    return view('landing-page');
})->name('landing.page');

// Redirect dashboard to authenticated users
Route::get('/dashboard', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

// Company Registration routes
Route::get('/register/company', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'showRegistrationForm'])->name('company.register.form');
Route::post('/register/company', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'register'])->name('company.register');
Route::post('/check-company-email', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'checkCompanyEmail'])->name('company.check-email');
Route::post('/check-owner-email', [App\Http\Controllers\Auth\CompanyRegistrationController::class, 'checkOwnerEmail'])->name('owner.check-email');



// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employee Management
    Route::middleware('ensure.company')->group(function () {
        Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
        Route::get('/employees-data', [EmployeeController::class, 'data'])->name('employees.data');
        Route::resource('employees', EmployeeController::class);
    });

    // Master Data Management
    Route::middleware('ensure.company')->group(function () {
        Route::get('/departments-data', [DepartmentController::class, 'data'])->name('departments.data');
        Route::resource('departments', DepartmentController::class);
        
        Route::get('/positions-data', [PositionController::class, 'data'])->name('positions.data');
        Route::resource('positions', PositionController::class);
    });

    // Payroll Management
    Route::get('/payrolls/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('/payrolls/generate', [PayrollController::class, 'generatePayroll'])->name('payrolls.generate.store');
    Route::get('/payrolls-data', [PayrollController::class, 'data'])->name('payrolls.data');
    Route::resource('payrolls', PayrollController::class);

    // Attendance Management
    Route::get('/attendance/check-in-out', [AttendanceController::class, 'checkInOut'])->name('attendance.check-in-out');
    Route::get('/attendance/current', [AttendanceController::class, 'current'])->name('attendance.current');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/calendar', [AttendanceCalendarController::class, 'index'])->name('attendance.calendar');
    Route::get('/attendance/calendar/data', [AttendanceCalendarController::class, 'getCalendarData'])->name('attendance.calendar.data');
    Route::get('/attendance-data', [AttendanceController::class, 'data'])->name('attendance.data');
    Route::resource('attendance', AttendanceController::class);

    // Leave Management
    Route::get('/leaves/approval', [LeaveController::class, 'approval'])->name('leaves.approval');
    Route::get('/leaves/approval-data', [LeaveController::class, 'approvalData'])->name('leaves.approval.data');
    Route::get('/leaves/approval-debug', [LeaveController::class, 'approvalData'])->name('leaves.approval.debug');
    Route::get('/leaves/balance', [LeaveController::class, 'balance'])->name('leaves.balance');
    Route::get('/leaves-data', [LeaveController::class, 'data'])->name('leaves.data');
    Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
    Route::resource('leaves', LeaveController::class);
    

    
    // Leave API Routes for AJAX
    Route::get('/api/leaves', [LeaveController::class, 'apiIndex'])->name('api.leaves.index');
    Route::get('/api/leaves/balance', [LeaveController::class, 'apiBalance'])->name('api.leaves.balance');
    Route::get('/api/leaves/{id}', [LeaveController::class, 'apiShow'])->name('api.leaves.show');
    Route::post('/api/leaves', [LeaveController::class, 'apiStore'])->name('api.leaves.store');
    Route::put('/api/leaves/{id}', [LeaveController::class, 'apiUpdate'])->name('api.leaves.update');
    Route::delete('/api/leaves/{id}', [LeaveController::class, 'apiDestroy'])->name('api.leaves.destroy');

    // Overtime Management
    Route::get('/overtimes-data', [OvertimeController::class, 'data'])->name('overtimes.data');
Route::get('/overtimes/approval-data', [OvertimeController::class, 'approvalData'])->name('overtimes.approval.data');
Route::get('/overtimes/approval', [OvertimeController::class, 'approval'])->name('overtimes.approval');
Route::get('/overtimes/statistics', [OvertimeController::class, 'statistics'])->name('overtimes.statistics');
Route::post('/overtimes/{id}/approve', [OvertimeController::class, 'approve'])->name('overtimes.approve');
Route::post('/overtimes/{id}/reject', [OvertimeController::class, 'reject'])->name('overtimes.reject');

Route::resource('overtimes', OvertimeController::class);

    // Attendance Reports
    Route::get('/reports', [AttendanceReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/individual', [AttendanceReportController::class, 'individual'])->name('reports.individual');
    Route::get('/reports/team', [AttendanceReportController::class, 'team'])->name('reports.team');
    Route::get('/reports/company', [AttendanceReportController::class, 'company'])->name('reports.company');
    Route::post('/reports/export', [AttendanceReportController::class, 'export'])->name('reports.export');

    // Payroll Management
    Route::post('/payrolls/{id}/approve', [PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('/payrolls/{id}/reject', [PayrollController::class, 'reject'])->name('payrolls.reject');
    Route::post('/payrolls/{id}/mark-paid', [PayrollController::class, 'markPaid'])->name('payrolls.mark-paid');
    Route::post('/payrolls/generate-all', [PayrollController::class, 'generateAll'])->name('payrolls.generate-all');
    Route::post('/payrolls/export', [PayrollController::class, 'export'])->name('payrolls.export');
    Route::post('/payrolls/calculate', [PayrollController::class, 'calculate'])->name('payrolls.calculate');

    // Settings & Configuration
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/company', [SettingsController::class, 'company'])->name('settings.company');
    Route::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.update-company');
    Route::get('/settings/payroll-policy', [SettingsController::class, 'payrollPolicy'])->name('settings.payroll-policy');
    Route::post('/settings/payroll-policy', [SettingsController::class, 'updatePayrollPolicy'])->name('settings.update-payroll-policy');
    Route::get('/settings/leave-policy', [SettingsController::class, 'leavePolicy'])->name('settings.leave-policy');
    Route::post('/settings/leave-policy', [SettingsController::class, 'updateLeavePolicy'])->name('settings.update-leave-policy');
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::get('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.update-password');
    Route::get('/settings/system', [SettingsController::class, 'system'])->name('settings.system');
    Route::post('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.update-system');
    Route::get('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
    Route::post('/settings/backup', [SettingsController::class, 'createBackup'])->name('settings.create-backup');
    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
    Route::post('/settings/users', [SettingsController::class, 'createUser'])->name('settings.create-user');
    Route::post('/settings/users/{id}', [SettingsController::class, 'updateUser'])->name('settings.update-user');
    Route::delete('/settings/users/{id}', [SettingsController::class, 'deleteUser'])->name('settings.delete-user');

    // Performance Management
Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
Route::get('/performance/kpi', [PerformanceController::class, 'kpi'])->name('performance.kpi');
Route::post('/performance/kpi', [PerformanceController::class, 'storeKPI'])->name('performance.store-kpi');
Route::post('/performance/kpi/{id}', [PerformanceController::class, 'updateKPI'])->name('performance.update-kpi');
Route::get('/performance/appraisal', [PerformanceController::class, 'appraisal'])->name('performance.appraisal');
Route::post('/performance/appraisal', [PerformanceController::class, 'createAppraisal'])->name('performance.create-appraisal');
Route::get('/performance/bonus', [PerformanceController::class, 'bonus'])->name('performance.bonus');
Route::post('/performance/bonus', [PerformanceController::class, 'calculateBonus'])->name('performance.calculate-bonus');
Route::get('/performance/goals', [PerformanceController::class, 'goals'])->name('performance.goals');
Route::post('/performance/goals', [PerformanceController::class, 'storeGoal'])->name('performance.store-goal');
Route::post('/performance/goals/{id}', [PerformanceController::class, 'updateGoal'])->name('performance.update-goal');
Route::get('/performance/reports', [PerformanceController::class, 'reports'])->name('performance.reports');

// Benefits Management
Route::get('/benefits', [BenefitController::class, 'index'])->name('benefits.index');
Route::get('/benefits/list', [BenefitController::class, 'benefits'])->name('benefits.benefits');
Route::get('/benefits/create', [BenefitController::class, 'create'])->name('benefits.create');
Route::post('/benefits', [BenefitController::class, 'store'])->name('benefits.store');
Route::get('/benefits/{id}/edit', [BenefitController::class, 'edit'])->name('benefits.edit');
Route::put('/benefits/{id}', [BenefitController::class, 'update'])->name('benefits.update');
Route::delete('/benefits/{id}', [BenefitController::class, 'destroy'])->name('benefits.destroy');
Route::get('/benefits/assignments', [BenefitController::class, 'assignments'])->name('benefits.assignments');
Route::post('/benefits/assign', [BenefitController::class, 'assignBenefit'])->name('benefits.assign');
Route::put('/benefits/assignments/{id}', [BenefitController::class, 'updateAssignment'])->name('benefits.update-assignment');
Route::delete('/benefits/assignments/{id}', [BenefitController::class, 'removeAssignment'])->name('benefits.remove-assignment');
Route::get('/benefits/employee/{employeeId}', [BenefitController::class, 'employeeBenefits'])->name('benefits.employee-benefits');
Route::get('/benefits/reports', [BenefitController::class, 'reports'])->name('benefits.reports');

    // Tax Management
    Route::get('/taxes/data', [TaxController::class, 'data'])->name('taxes.data');
    Route::resource('taxes', TaxController::class);
    Route::post('/taxes/calculate-for-payroll', [TaxController::class, 'calculateForPayroll'])->name('taxes.calculate-for-payroll');
    Route::get('/taxes/report', [TaxController::class, 'report'])->name('taxes.report');
    Route::get('/taxes/export', [TaxController::class, 'export'])->name('taxes.export');
    
    // Tax Reports (Phase 4.4)
    Route::get('/taxes/monthly-report', [TaxController::class, 'monthlyTaxReport'])->name('taxes.monthly-report');
    Route::get('/taxes/annual-summary', [TaxController::class, 'annualTaxSummary'])->name('taxes.annual-summary');
    Route::get('/taxes/payment-report', [TaxController::class, 'taxPaymentReport'])->name('taxes.payment-report');
    Route::get('/taxes/certificate-report', [TaxController::class, 'taxCertificateReport'])->name('taxes.certificate-report');
    Route::get('/taxes/compliance-report', [TaxController::class, 'taxComplianceReport'])->name('taxes.compliance-report');
    Route::get('/taxes/audit-trail', [TaxController::class, 'taxAuditTrail'])->name('taxes.audit-trail');

    // BPJS Management
    Route::resource('bpjs', BpjsController::class);
    Route::post('/bpjs/calculate-for-payroll', [BpjsController::class, 'calculateForPayroll'])->name('bpjs.calculate-for-payroll');
    Route::get('/bpjs/report', [BpjsController::class, 'report'])->name('bpjs.report');
    Route::get('/bpjs/export', [BpjsController::class, 'export'])->name('bpjs.export');

    // Export Functionality
    Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');
    Route::get('/exports/employees', [ExportController::class, 'exportEmployees'])->name('exports.employees');
    Route::get('/exports/payrolls', [ExportController::class, 'exportPayrolls'])->name('exports.payrolls');
    Route::get('/exports/attendance', [ExportController::class, 'exportAttendance'])->name('exports.attendance');
    Route::get('/exports/leaves', [ExportController::class, 'exportLeaves'])->name('exports.leaves');
    Route::get('/exports/taxes', [ExportController::class, 'exportTaxes'])->name('exports.taxes');
    Route::get('/exports/bpjs', [ExportController::class, 'exportBpjs'])->name('exports.bpjs');
    Route::post('/exports/all', [ExportController::class, 'exportAll'])->name('exports.all');

    // Bank Integration (Phase 5.1)
    Route::resource('bank-accounts', BankAccountController::class);
    Route::post('/bank-accounts/{bankAccount}/toggle-status', [BankAccountController::class, 'toggleStatus'])->name('bank-accounts.toggle-status');
    Route::post('/bank-accounts/{bankAccount}/set-primary', [BankAccountController::class, 'setPrimary'])->name('bank-accounts.set-primary');
    Route::get('/bank-accounts/get-employee-accounts', [BankAccountController::class, 'getEmployeeAccounts'])->name('bank-accounts.get-employee-accounts');

    Route::resource('salary-transfers', SalaryTransferController::class);
    Route::post('/salary-transfers/{salaryTransfer}/process', [SalaryTransferController::class, 'process'])->name('salary-transfers.process');
    Route::post('/salary-transfers/{salaryTransfer}/complete', [SalaryTransferController::class, 'complete'])->name('salary-transfers.complete');
    Route::post('/salary-transfers/{salaryTransfer}/cancel', [SalaryTransferController::class, 'cancel'])->name('salary-transfers.cancel');
    Route::post('/salary-transfers/{salaryTransfer}/retry', [SalaryTransferController::class, 'retry'])->name('salary-transfers.retry');
    Route::post('/salary-transfers/batch-transfer', [SalaryTransferController::class, 'batchTransfer'])->name('salary-transfers.batch-transfer');
    Route::post('/salary-transfers/import-bank-statement', [SalaryTransferController::class, 'importBankStatement'])->name('salary-transfers.import-bank-statement');
    Route::get('/salary-transfers/statistics', [SalaryTransferController::class, 'statistics'])->name('salary-transfers.statistics');

    // External System Integration (Phase 5.2)
    Route::resource('integrations', ExternalIntegrationController::class);
    Route::post('/integrations/{integration}/test-connection', [ExternalIntegrationController::class, 'testConnection'])->name('integrations.test-connection');
    Route::post('/integrations/{integration}/sync-now', [ExternalIntegrationController::class, 'syncNow'])->name('integrations.sync-now');
    Route::get('/integrations/{integration}/logs', [ExternalIntegrationController::class, 'logs'])->name('integrations.logs');
    Route::post('/integrations/{integration}/toggle-status', [ExternalIntegrationController::class, 'toggleStatus'])->name('integrations.toggle-status');

    // Data Import/Export (Phase 5.3)
    Route::get('/import', [DataImportController::class, 'index'])->name('import.index');
    Route::post('/import/employees', [DataImportController::class, 'importEmployees'])->name('import.employees');
    Route::post('/import/payroll', [DataImportController::class, 'importPayroll'])->name('import.payroll');
    Route::post('/import/attendance', [DataImportController::class, 'importAttendance'])->name('import.attendance');
    Route::get('/import/template/{type}', [DataImportController::class, 'downloadTemplate'])->name('import.template');
    Route::post('/import/validate', [DataImportController::class, 'validateImport'])->name('import.validate');

    // Salary Components Management
    Route::middleware('ensure.company')->group(function () {
        Route::get('/salary-components-data', [App\Http\Controllers\SalaryComponentController::class, 'data'])->name('salary-components.data');
        Route::post('/salary-components/{salaryComponent}/toggle-status', [App\Http\Controllers\SalaryComponentController::class, 'toggleStatus'])->name('salary-components.toggle-status');
        Route::post('/salary-components/update-sort-order', [App\Http\Controllers\SalaryComponentController::class, 'updateSortOrder'])->name('salary-components.update-sort-order');
        Route::post('/salary-components/bulk-toggle-status', [App\Http\Controllers\SalaryComponentController::class, 'bulkToggleStatus'])->name('salary-components.bulk-toggle-status');
        Route::post('/salary-components/bulk-delete', [App\Http\Controllers\SalaryComponentController::class, 'bulkDelete'])->name('salary-components.bulk-delete');
        Route::get('/salary-components-test', function() { return view('salary-components.test'); })->name('salary-components.test');
        Route::get('/salary-components-debug', function() { return view('salary-components.debug'); })->name('salary-components.debug');
        Route::resource('salary-components', App\Http\Controllers\SalaryComponentController::class);
    });

    // Employee Salary Components Management (Embedded in Employee Detail)
    Route::middleware('ensure.company')->group(function () {
        Route::post('/employees/{employee}/salary-components', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'store'])->name('employee-salary-components.store');
        Route::post('/employees/{employee}/salary-components/{employeeSalaryComponent}', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'update'])->name('employee-salary-components.update');
        Route::delete('/employees/{employee}/salary-components/{employeeSalaryComponent}', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'destroy'])->name('employee-salary-components.destroy');
        Route::post('/employees/{employee}/salary-components/{employeeSalaryComponent}/toggle-status', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'toggleStatus'])->name('employee-salary-components.toggle-status');
        Route::get('/employees/{employee}/salary-components/{employeeSalaryComponent}', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'show'])->name('employee-salary-components.show');
        Route::get('/employees/{employee}/salary-components/{employeeSalaryComponent}/edit', [App\Http\Controllers\EmployeeSalaryComponentController::class, 'edit'])->name('employee-salary-components.edit');
    });

    // Employee Salary Component Management (Standalone Module)
    Route::middleware('ensure.company')->group(function () {
        Route::resource('employee-salary-component-management', App\Http\Controllers\EmployeeSalaryComponentManagementController::class);
        Route::post('/employee-salary-component-management/bulk-assign', [App\Http\Controllers\EmployeeSalaryComponentManagementController::class, 'bulkAssign'])->name('employee-salary-component-management.bulk-assign');
        Route::post('/employee-salary-component-management/{employeeSalaryComponent}/toggle-status', [App\Http\Controllers\EmployeeSalaryComponentManagementController::class, 'toggleStatus'])->name('employee-salary-component-management.toggle-status');
    });
});
