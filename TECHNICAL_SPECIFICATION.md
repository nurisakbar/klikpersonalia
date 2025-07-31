# 🔧 TECHNICAL SPECIFICATION - APLIKASI PAYROLL KLIKMEDIS

## 🎯 **PROJECT OVERVIEW**

**Application Name:** Aplikasi Payroll KlikMedis  
**Framework:** Laravel 12  
**Frontend:** AdminLTE 3 + Bootstrap 5  
**Database:** SQLite (Development) / MySQL (Production)  
**Authentication:** Laravel Breeze  
**Version:** 1.0  

---

## 🏗️ **SYSTEM ARCHITECTURE**

### **Technology Stack**
```
Backend:
├── Laravel 12 (PHP 8.2+)
├── SQLite/MySQL Database
├── Laravel Breeze (Authentication)
└── Laravel Queue (Background Jobs)

Frontend:
├── AdminLTE 3.2.0
├── Bootstrap 5.3
├── jQuery 3.7
├── DataTables 1.13
├── Chart.js 4.4
└── Font Awesome 6.4

Development Tools:
├── Composer (PHP Dependencies)
├── NPM (Frontend Assets)
├── Git (Version Control)
└── XAMPP (Local Development)
```

### **Directory Structure**
```
aplikasi_payrool_klikmedis/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php
│   │   ├── EmployeeController.php
│   │   ├── PayrollController.php
│   │   ├── AttendanceController.php
│   │   └── ReportController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Employee.php
│   │   ├── Payroll.php
│   │   ├── Attendance.php
│   │   └── Department.php
│   └── Services/
│       ├── PayrollCalculationService.php
│       ├── TaxCalculationService.php
│       └── AttendanceService.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── dashboard/
│   │   ├── employees/
│   │   ├── payroll/
│   │   └── attendance/
│   └── assets/
└── routes/
    └── web.php
```

---

## 🗄️ **DATABASE DESIGN**

### **Core Tables**

#### **1. users**
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **2. employees**
```sql
CREATE TABLE employees (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    address TEXT NULL,
    join_date DATE NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    position VARCHAR(100) NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    emergency_contact VARCHAR(255) NULL,
    bank_name VARCHAR(100) NULL,
    bank_account VARCHAR(50) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);
```

#### **3. departments**
```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    manager_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (manager_id) REFERENCES employees(id)
);
```

#### **4. payrolls**
```sql
CREATE TABLE payrolls (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    period VARCHAR(7) NOT NULL, -- Format: YYYY-MM
    basic_salary DECIMAL(12,2) NOT NULL,
    allowance DECIMAL(12,2) DEFAULT 0,
    overtime DECIMAL(12,2) DEFAULT 0,
    bonus DECIMAL(12,2) DEFAULT 0,
    deduction DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    bpjs_amount DECIMAL(12,2) DEFAULT 0,
    total_salary DECIMAL(12,2) NOT NULL,
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    payment_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);
```

#### **5. attendances**
```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    total_hours DECIMAL(4,2) DEFAULT 0,
    overtime_hours DECIMAL(4,2) DEFAULT 0,
    status ENUM('present', 'absent', 'late', 'half_day', 'leave') DEFAULT 'present',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    UNIQUE KEY unique_employee_date (employee_id, date)
);
```

#### **6. leaves**
```sql
CREATE TABLE leaves (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type ENUM('annual', 'sick', 'maternity', 'paternity', 'other') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

### **Configuration Tables**

#### **7. salary_components**
```sql
CREATE TABLE salary_components (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('allowance', 'deduction') NOT NULL,
    is_taxable BOOLEAN DEFAULT FALSE,
    is_bpjs_taxable BOOLEAN DEFAULT FALSE,
    percentage DECIMAL(5,2) NULL,
    fixed_amount DECIMAL(12,2) NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### **8. tax_brackets**
```sql
CREATE TABLE tax_brackets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    min_amount DECIMAL(12,2) NOT NULL,
    max_amount DECIMAL(12,2) NULL,
    percentage DECIMAL(5,2) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## 🔌 **API SPECIFICATIONS**

### **Authentication Endpoints**
```
POST /api/login
POST /api/logout
POST /api/refresh
GET  /api/user
```

### **Employee Endpoints**
```
GET    /api/employees              # List employees
POST   /api/employees              # Create employee
GET    /api/employees/{id}         # Get employee
PUT    /api/employees/{id}         # Update employee
DELETE /api/employees/{id}         # Delete employee
GET    /api/employees/{id}/payroll # Get employee payroll history
```

### **Payroll Endpoints**
```
GET    /api/payrolls                    # List payrolls
POST   /api/payrolls                    # Create payroll
GET    /api/payrolls/{id}               # Get payroll
PUT    /api/payrolls/{id}               # Update payroll
DELETE /api/payrolls/{id}               # Delete payroll
POST   /api/payrolls/generate           # Generate monthly payroll
POST   /api/payrolls/{id}/approve       # Approve payroll
POST   /api/payrolls/{id}/pay           # Mark as paid
```

### **Attendance Endpoints**
```
GET    /api/attendances                 # List attendances
POST   /api/attendances                 # Create attendance
GET    /api/attendances/{id}            # Get attendance
PUT    /api/attendances/{id}            # Update attendance
DELETE /api/attendances/{id}            # Delete attendance
POST   /api/attendances/check-in        # Employee check-in
POST   /api/attendances/check-out       # Employee check-out
GET    /api/attendances/employee/{id}   # Employee attendance history
```

### **Report Endpoints**
```
GET /api/reports/payroll-summary        # Payroll summary report
GET /api/reports/attendance-summary     # Attendance summary report
GET /api/reports/tax-report             # Tax report
GET /api/reports/bpjs-report            # BPJS report
GET /api/reports/employee/{id}/payslip  # Employee payslip
```

---

## 🧮 **BUSINESS LOGIC SPECIFICATIONS**

### **Payroll Calculation Algorithm**
```php
// Basic Payroll Calculation
$basicSalary = $employee->basic_salary;
$allowance = $payroll->allowance;
$overtime = $payroll->overtime;
$bonus = $payroll->bonus;
$deduction = $payroll->deduction;

// Calculate taxable income
$taxableIncome = $basicSalary + $allowance + $overtime + $bonus;

// Calculate tax (PPh 21)
$taxAmount = $this->calculateTax($taxableIncome, $employee);

// Calculate BPJS
$bpjsAmount = $this->calculateBPJS($taxableIncome, $employee);

// Calculate total salary
$totalSalary = $taxableIncome - $taxAmount - $bpjsAmount - $deduction;
```

### **Tax Calculation (PPh 21)**
```php
// PTKP (Penghasilan Tidak Kena Pajak)
$ptkp = [
    'TK/0' => 54000000,  // Single, no dependents
    'TK/1' => 58500000,  // Single, 1 dependent
    'TK/2' => 63000000,  // Single, 2 dependents
    'TK/3' => 67500000,  // Single, 3 dependents
    'K/0' => 58500000,   // Married, no dependents
    'K/1' => 63000000,   // Married, 1 dependent
    'K/2' => 67500000,   // Married, 2 dependents
    'K/3' => 72000000,   // Married, 3 dependents
];

// Tax brackets (2024)
$taxBrackets = [
    ['min' => 0, 'max' => 60000000, 'rate' => 0.05],
    ['min' => 60000000, 'max' => 250000000, 'rate' => 0.15],
    ['min' => 250000000, 'max' => 500000000, 'rate' => 0.25],
    ['min' => 500000000, 'max' => 5000000000, 'rate' => 0.30],
    ['min' => 5000000000, 'max' => null, 'rate' => 0.35],
];
```

### **BPJS Calculation**
```php
// BPJS Kesehatan (4% of basic salary)
$bpjsKesehatan = $basicSalary * 0.04;

// BPJS Ketenagakerjaan
$bpjsJHT = $basicSalary * 0.02;  // Jaminan Hari Tua (2%)
$bpjsJKK = $basicSalary * 0.0024; // Jaminan Kecelakaan Kerja (0.24%)
$bpjsJKM = $basicSalary * 0.003;  // Jaminan Kematian (0.3%)
$bpjsJP = $basicSalary * 0.01;    // Jaminan Pensiun (1%)

$totalBPJS = $bpjsKesehatan + $bpjsJHT + $bpjsJKK + $bpjsJKM + $bpjsJP;
```

### **Overtime Calculation**
```php
// Overtime rates
$overtimeRates = [
    'weekday' => 1.5,  // 1.5x for weekday overtime
    'weekend' => 2.0,  // 2x for weekend overtime
    'holiday' => 3.0,  // 3x for holiday overtime
];

// Calculate overtime pay
$overtimePay = $hourlyRate * $overtimeHours * $overtimeRates[$dayType];
```

---

## 🔐 **SECURITY SPECIFICATIONS**

### **Authentication & Authorization**
- **Laravel Breeze** for authentication
- **Session-based** authentication
- **CSRF protection** on all forms
- **Route middleware** protection
- **Role-based access control** (RBAC)

### **Data Protection**
- **Input validation** using Laravel validation rules
- **SQL injection protection** via Eloquent ORM
- **XSS protection** via Blade templating
- **File upload security** with validation
- **Data encryption** for sensitive information

### **Access Control Matrix**
```
Role: Admin
├── Full access to all modules
├── User management
├── System configuration
└── Reports generation

Role: HR Manager
├── Employee management
├── Payroll management
├── Attendance management
└── HR reports

Role: Payroll Officer
├── Payroll processing
├── Payroll reports
└── Basic employee view

Role: Employee
├── View own profile
├── View own payslip
├── Submit leave requests
└── View own attendance
```

---

## 📱 **FRONTEND SPECIFICATIONS**

### **Responsive Design**
- **Mobile-first** approach
- **Bootstrap 5** grid system
- **AdminLTE 3** responsive components
- **Breakpoints:**
  - Mobile: < 768px
  - Tablet: 768px - 1024px
  - Desktop: > 1024px

### **UI Components**
- **DataTables** for listing pages
- **Chart.js** for analytics
- **Bootstrap modals** for forms
- **SweetAlert2** for notifications
- **Flatpickr** for date pickers
- **Select2** for dropdowns

### **JavaScript Libraries**
```html
<!-- Core Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Additional Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
```

---

## 🚀 **DEPLOYMENT SPECIFICATIONS**

### **Environment Requirements**
```
Server: Ubuntu 20.04+ / CentOS 8+
PHP: 8.2+
MySQL: 8.0+ / PostgreSQL 13+
Web Server: Apache 2.4+ / Nginx 1.18+
SSL Certificate: Required
Memory: 2GB+ RAM
Storage: 20GB+ SSD
```

### **Deployment Process**
1. **Code Deployment**
   ```bash
   git clone [repository]
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Database Setup**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. **File Permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chown -R www-data:www-data storage/
   chown -R www-data:www-data bootstrap/cache/
   ```

### **Performance Optimization**
- **OPcache** enabled
- **Redis** for caching
- **Queue workers** for background jobs
- **CDN** for static assets
- **Database indexing** optimization
- **Image optimization** for uploads

---

## 📊 **MONITORING & LOGGING**

### **Application Logging**
- **Laravel Log** for application errors
- **Database query logging** for performance
- **User activity logging** for audit trail
- **Payroll processing logs** for compliance

### **Performance Monitoring**
- **Response time** monitoring
- **Database query** performance
- **Memory usage** tracking
- **Error rate** monitoring

### **Health Checks**
- **Database connectivity**
- **Queue worker status**
- **Storage space** monitoring
- **SSL certificate** expiration

---

## 🔄 **MAINTENANCE & UPDATES**

### **Regular Maintenance**
- **Daily:** Database backups
- **Weekly:** Log rotation
- **Monthly:** Security updates
- **Quarterly:** Performance review

### **Update Process**
1. **Development testing**
2. **Staging deployment**
3. **Production backup**
4. **Production deployment**
5. **Post-deployment testing**

---

**Document Version:** 1.0  
**Last Updated:** July 31, 2025  
**Next Review:** August 31, 2025 