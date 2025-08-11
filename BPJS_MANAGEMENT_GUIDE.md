# 🏥 BPJS Management System Guide

## 📋 Overview

The BPJS Management System is a comprehensive module for managing Indonesian social security contributions (BPJS Kesehatan and BPJS Ketenagakerjaan) within the payroll application. This system handles calculation, tracking, reporting, and payment management for both health and employment insurance programs.

## 🏗️ Architecture

### Core Components

1. **BPJS Model** (`app/Models/Bpjs.php`)
   - Handles BPJS calculations and data management
   - Contains constants for rates and maximum base salaries
   - Provides calculation methods for both BPJS types

2. **BPJS Controller** (`app/Http/Controllers/BpjsController.php`)
   - Manages CRUD operations for BPJS records
   - Handles bulk calculation for payroll periods
   - Generates reports and exports

3. **Database Schema**
   - `bpjs` table: Stores BPJS contribution records
   - Enhanced `employees` table: BPJS-related fields

4. **Views** (`resources/views/bpjs/`)
   - Index, Create, Show, Edit, and Report views
   - Real-time calculation previews
   - Interactive charts and statistics

## 🧮 Calculation Algorithm

### BPJS Kesehatan (Health Insurance)

**Formula:**
```
Employee Contribution = Base Salary × 1%
Company Contribution = Base Salary × 4%
Total Contribution = Employee + Company Contribution
```

**Rules:**
- Maximum base salary: Rp 12,000,000 (2024)
- Employee rate: 1% of base salary
- Company rate: 4% of base salary
- Capped at maximum base salary

### BPJS Ketenagakerjaan (Employment Insurance)

**Components:**

1. **JHT (Jaminan Hari Tua - Old Age Security)**
   - Employee: 2% of base salary
   - Company: 3.7% of base salary

2. **JKK (Jaminan Kecelakaan Kerja - Work Accident Insurance)**
   - Company: 0.24% of base salary (variable based on risk)

3. **JKM (Jaminan Kematian - Death Insurance)**
   - Company: 0.3% of base salary

4. **JP (Jaminan Pensiun - Pension Insurance)**
   - Employee: 1% of base salary
   - Company: 2% of base salary

**Formula:**
```
Employee Contribution = JHT Employee + JP Employee
Company Contribution = JHT Company + JKK Company + JKM Company + JP Company
Total Contribution = Employee + Company Contribution
```

**Rules:**
- Maximum base salary: Rp 12,000,000 (2024)
- All rates are based on 2024 regulations
- JKK rate may vary based on company risk level

## 🎯 Features

### 1. Individual BPJS Management
- Create, edit, view, and delete BPJS records
- Real-time calculation preview
- Status tracking (pending, calculated, paid, verified)
- Payment date management

### 2. Bulk BPJS Calculation
- Generate BPJS records for all employees in a payroll period
- Support for both BPJS types or individual types
- Automatic calculation based on employee base salary
- Duplicate prevention

### 3. Advanced Filtering
- Filter by period, type, status, and employee
- Auto-submit filters for better UX
- Clear filter functionality

### 4. Comprehensive Reporting
- Summary statistics with info boxes
- Detailed contribution breakdown
- Interactive charts (contribution distribution, type distribution)
- Export functionality (CSV format)

### 5. Employee BPJS Status
- BPJS number management
- Active/inactive status tracking
- Effective date tracking
- Notes and comments

## 📊 Database Schema

### BPJS Table
```sql
CREATE TABLE bpjs (
    id UUID PRIMARY KEY,
    company_id UUID,
    employee_id UUID,
    payroll_id UUID NULLABLE,
    bpjs_period VARCHAR(7), -- Format: YYYY-MM
    bpjs_type ENUM('kesehatan', 'ketenagakerjaan'),
    employee_contribution DECIMAL(15,2),
    company_contribution DECIMAL(15,2),
    total_contribution DECIMAL(15,2),
    base_salary DECIMAL(15,2),
    contribution_rate_employee DECIMAL(6,4),
    contribution_rate_company DECIMAL(6,4),
    status ENUM('pending', 'calculated', 'paid', 'verified'),
    payment_date DATE NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Enhanced Employees Table
```sql
ALTER TABLE employees ADD COLUMN (
    bpjs_kesehatan_number VARCHAR(255) NULLABLE,
    bpjs_ketenagakerjaan_number VARCHAR(255) NULLABLE,
    bpjs_kesehatan_active BOOLEAN DEFAULT TRUE,
    bpjs_ketenagakerjaan_active BOOLEAN DEFAULT TRUE,
    bpjs_effective_date DATE NULLABLE,
    bpjs_notes TEXT NULLABLE
);
```

## 🎨 User Interface

### 1. BPJS Index Page
- Summary cards showing statistics
- Advanced filtering options
- Bulk calculation form
- DataTable with pagination
- Action buttons for CRUD operations

### 2. Create/Edit Forms
- Employee selection with BPJS status validation
- Real-time calculation preview
- BPJS information cards
- Form validation and error handling

### 3. Detail View
- Comprehensive BPJS information
- Contribution breakdown (especially for Ketenagakerjaan)
- Employee information sidebar
- Related payroll information
- Payment status tracking

### 4. Report Page
- Summary statistics
- Interactive charts using Chart.js
- Detailed records table
- Export functionality
- Period and type filtering

## 🔧 API Endpoints

### Resource Routes
```php
Route::resource('bpjs', BpjsController::class);
```

### Custom Routes
```php
Route::post('/bpjs/calculate-for-payroll', [BpjsController::class, 'calculateForPayroll']);
Route::get('/bpjs/report', [BpjsController::class, 'report']);
Route::get('/bpjs/export', [BpjsController::class, 'export']);
```

### Available Methods
- `GET /bpjs` - List BPJS records
- `GET /bpjs/create` - Show create form
- `POST /bpjs` - Store new BPJS record
- `GET /bpjs/{id}` - Show BPJS details
- `GET /bpjs/{id}/edit` - Show edit form
- `PUT /bpjs/{id}` - Update BPJS record
- `DELETE /bpjs/{id}` - Delete BPJS record

## 📈 Reports & Analytics

### 1. Summary Statistics
- Total BPJS Kesehatan records
- Total BPJS Ketenagakerjaan records
- Total employee contributions
- Total company contributions

### 2. Interactive Charts
- **Contribution Distribution**: Doughnut chart showing employee vs company contributions
- **Type Distribution**: Pie chart showing BPJS Kesehatan vs Ketenagakerjaan records

### 3. Export Functionality
- CSV export with detailed records
- Filtered by period and type
- Includes all relevant BPJS information

## 🔐 Security & Permissions

### Role-Based Access
- **Admin/HR**: Full access to all BPJS features
- **Manager**: View and limited edit access
- **Employee**: View own BPJS records only

### Data Isolation
- Company-based data isolation
- Users can only access BPJS records from their company
- Secure filtering and validation

## 🧪 Testing

### Unit Tests
- BPJS calculation accuracy
- Model relationships
- Validation rules

### Integration Tests
- CRUD operations
- Bulk calculation functionality
- Report generation
- Export functionality

### User Acceptance Tests
- End-to-end workflow testing
- UI/UX validation
- Performance testing

## 🚀 Performance Optimization

### Database Optimization
- Proper indexing on frequently queried columns
- Efficient relationships and eager loading
- Query optimization for large datasets

### Frontend Optimization
- Lazy loading for large tables
- AJAX-based filtering
- Cached calculation results

## 🔄 Workflow

### 1. Monthly BPJS Processing
```
1. Generate payroll for the month
2. Run bulk BPJS calculation
3. Review and verify calculations
4. Update payment status
5. Generate reports
6. Export for payment processing
```

### 2. Individual BPJS Management
```
1. Select employee and period
2. Choose BPJS type
3. Enter base salary (auto-filled from employee data)
4. Review calculation preview
5. Save BPJS record
6. Update status as needed
```

## 📝 Usage Examples

### Creating BPJS Record
```php
// Calculate BPJS Kesehatan
$calculation = Bpjs::calculateKesehatan($employee, $baseSalary, $period);

// Create BPJS record
Bpjs::create([
    'company_id' => $companyId,
    'employee_id' => $employeeId,
    'bpjs_period' => $period,
    'bpjs_type' => 'kesehatan',
    'employee_contribution' => $calculation['employee_contribution'],
    'company_contribution' => $calculation['company_contribution'],
    'total_contribution' => $calculation['total_contribution'],
    'base_salary' => $calculation['base_salary'],
    'status' => 'calculated'
]);
```

### Bulk Calculation
```php
// Calculate BPJS for all employees in a payroll period
$employees = Employee::forCompany($companyId)->get();
$payrolls = Payroll::forCompany($companyId)->forPeriod($period)->get();

foreach ($employees as $employee) {
    if ($employee->bpjs_kesehatan_active) {
        $this->createBpjsRecord($companyId, $employee, $payroll, $baseSalary, 'kesehatan', $period);
    }
}
```

## 🐛 Error Handling

### Common Issues
1. **Duplicate Records**: Prevention of duplicate BPJS records for same employee, period, and type
2. **Invalid Base Salary**: Validation of base salary input
3. **Inactive Employee**: Warning when employee is not active for selected BPJS type
4. **Missing Payroll**: Error handling when payroll data is not found

### Error Messages
- Clear validation error messages
- User-friendly error notifications
- Detailed error logging for debugging

## 🔮 Future Enhancements

### Planned Features
1. **BPJS Online Integration**: Direct integration with BPJS online portal
2. **Automated Payment**: Automatic payment processing
3. **Certificate Generation**: Digital BPJS certificates
4. **Mobile App**: BPJS management on mobile devices
5. **Advanced Analytics**: Predictive analytics and cost projections

### Technical Improvements
1. **API Rate Limiting**: Protection against abuse
2. **Caching**: Improved performance with Redis caching
3. **Webhooks**: Real-time updates from BPJS systems
4. **Audit Trail**: Comprehensive logging of all changes

## 📚 Additional Resources

### Documentation
- [BPJS Official Website](https://www.bpjs-kesehatan.go.id/)
- [BPJS Ketenagakerjaan](https://www.bpjsketenagakerjaan.go.id/)
- [Indonesian Labor Law](https://jdih.kemenaker.go.id/)

### Support
- Technical documentation in code comments
- User manual with screenshots
- Video tutorials for complex workflows
- Community forum for user support

---

**Last Updated:** July 31, 2025  
**Version:** 1.0  
**Status:** Complete ✅ 