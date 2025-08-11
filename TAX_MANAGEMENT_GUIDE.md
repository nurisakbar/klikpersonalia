# 📊 TAX MANAGEMENT SYSTEM GUIDE

## 🎯 **Overview**
Sistem Tax Management (PPh 21) untuk aplikasi payroll KlikMedis yang mengimplementasikan perhitungan pajak sesuai regulasi Indonesia tahun 2024.

---

## 🏗️ **System Architecture**

### **Models**
- **Tax Model** - Menangani perhitungan dan penyimpanan data pajak
- **Employee Model** - Menyimpan PTKP status karyawan
- **Payroll Model** - Terintegrasi dengan perhitungan pajak

### **Controllers**
- **TaxController** - Mengelola CRUD dan perhitungan pajak
- **PayrollController** - Terintegrasi dengan tax calculation

### **Views**
- **Index** - Daftar perhitungan pajak dengan filter
- **Create** - Form pembuatan perhitungan pajak
- **Show** - Detail perhitungan pajak
- **Edit** - Form edit perhitungan pajak
- **Report** - Laporan pajak komprehensif

---

## 🧮 **Tax Calculation Algorithm**

### **PPh 21 Calculation Steps**
1. **Taxable Income** = Basic Salary + Allowances + Overtime + Bonus
2. **PTKP Amount** = Based on employee's PTKP status
3. **Taxable Base** = Taxable Income - PTKP Amount
4. **Tax Amount** = Progressive tax calculation based on brackets

### **PTKP Status (2024)**
```
TK/0 - Single, no dependents: Rp 54,000,000
TK/1 - Single, 1 dependent: Rp 58,500,000
TK/2 - Single, 2 dependents: Rp 63,000,000
TK/3 - Single, 3 dependents: Rp 67,500,000
K/0 - Married, no dependents: Rp 58,500,000
K/1 - Married, 1 dependent: Rp 63,000,000
K/2 - Married, 2 dependents: Rp 67,500,000
K/3 - Married, 3 dependents: Rp 72,000,000
```

### **Tax Brackets (2024)**
```
0 - 60,000,000: 5%
60,000,000 - 250,000,000: 15%
250,000,000 - 500,000,000: 25%
500,000,000 - 5,000,000,000: 30%
Above 5,000,000,000: 35%
```

---

## 🚀 **Features**

### **1. Tax Calculation**
- ✅ Manual tax calculation per employee
- ✅ Bulk tax calculation for all employees
- ✅ Automatic calculation from payroll data
- ✅ Real-time calculation preview

### **2. Tax Management**
- ✅ CRUD operations for tax records
- ✅ Status management (Pending, Calculated, Paid, Verified)
- ✅ PTKP status configuration
- ✅ Tax period management

### **3. Tax Reports**
- ✅ Individual tax reports
- ✅ Company-wide tax summary
- ✅ Tax bracket distribution
- ✅ Status summary
- ✅ Export to Excel/CSV

### **4. Integration**
- ✅ Payroll integration
- ✅ Employee data integration
- ✅ Multi-company support
- ✅ Role-based access control

---

## 📋 **Database Schema**

### **Taxes Table**
```sql
CREATE TABLE taxes (
    id UUID PRIMARY KEY,
    company_id UUID,
    employee_id UUID,
    payroll_id UUID NULL,
    tax_period VARCHAR(7),
    taxable_income DECIMAL(15,2),
    ptkp_status VARCHAR(10),
    ptkp_amount DECIMAL(15,2),
    taxable_base DECIMAL(15,2),
    tax_amount DECIMAL(15,2),
    tax_bracket VARCHAR(50),
    tax_rate DECIMAL(5,2),
    status ENUM('pending', 'calculated', 'paid', 'verified'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **Employees Table (Updated)**
```sql
ALTER TABLE employees ADD COLUMN ptkp_status VARCHAR(10) DEFAULT 'TK/0';
ALTER TABLE employees ADD COLUMN tax_notes TEXT;
```

---

## 🎮 **User Interface**

### **Navigation Menu**
```
Tax Management
├── Tax Calculations
├── New Tax Calculation
└── Tax Reports
```

### **Key Pages**
1. **Tax Index** - List semua perhitungan pajak dengan filter
2. **Tax Create** - Form pembuatan perhitungan pajak baru
3. **Tax Show** - Detail lengkap perhitungan pajak
4. **Tax Edit** - Form edit perhitungan pajak
5. **Tax Report** - Laporan pajak komprehensif

---

## 🔧 **API Endpoints**

### **Tax Management Routes**
```php
// Resource routes
Route::resource('taxes', TaxController::class);

// Additional routes
Route::post('/taxes/calculate-for-payroll', [TaxController::class, 'calculateForPayroll']);
Route::get('/taxes/report', [TaxController::class, 'report']);
Route::get('/taxes/export', [TaxController::class, 'export']);
```

### **Available Methods**
- `GET /taxes` - List tax calculations
- `GET /taxes/create` - Create form
- `POST /taxes` - Store new tax calculation
- `GET /taxes/{id}` - Show tax details
- `GET /taxes/{id}/edit` - Edit form
- `PUT /taxes/{id}` - Update tax calculation
- `DELETE /taxes/{id}` - Delete tax calculation
- `POST /taxes/calculate-for-payroll` - Bulk calculation
- `GET /taxes/report` - Tax report
- `GET /taxes/export` - Export to Excel

---

## 📊 **Reports & Analytics**

### **Tax Summary Report**
- Total employees with tax calculations
- Total taxable income
- Total tax amount
- Average tax rate
- Status distribution

### **Tax Bracket Distribution**
- Breakdown by tax brackets
- Employee count per bracket
- Tax amount per bracket

### **Status Summary**
- Pending calculations
- Calculated taxes
- Paid taxes
- Verified taxes

---

## 🔐 **Security & Permissions**

### **Role-based Access**
- **Admin** - Full access to all tax features
- **HR** - Full access to tax management
- **Manager** - View access to tax reports
- **Employee** - View own tax calculations

### **Data Isolation**
- Company-based data isolation
- Employee can only see their own tax data
- Multi-company support

---

## 🧪 **Testing**

### **Unit Tests**
- Tax calculation accuracy
- PTKP calculation
- Tax bracket determination
- Status management

### **Integration Tests**
- Payroll integration
- Employee data integration
- Report generation
- Export functionality

---

## 📈 **Performance Optimization**

### **Database Optimization**
- Indexed foreign keys
- Composite indexes for filtering
- Efficient queries for reports

### **Caching Strategy**
- Tax calculation results
- Report data caching
- PTKP and bracket data

---

## 🔄 **Workflow**

### **Tax Calculation Workflow**
1. **Setup** - Configure employee PTKP status
2. **Calculation** - Generate tax calculations
3. **Review** - Review and verify calculations
4. **Approval** - Approve tax calculations
5. **Payment** - Mark as paid
6. **Reporting** - Generate tax reports

### **Bulk Calculation Process**
1. Select month and year
2. System checks existing payroll data
3. Calculates tax for all employees
4. Creates tax records
5. Generates summary report

---

## 📚 **Usage Examples**

### **Manual Tax Calculation**
```php
// Calculate tax for an employee
$employee = Employee::find(1);
$taxableIncome = 80000000; // Rp 80,000,000
$taxCalculation = Tax::calculatePPh21($employee, $taxableIncome);

// Result
[
    'ptkp_status' => 'TK/0',
    'ptkp_amount' => 54000000,
    'taxable_base' => 26000000,
    'tax_amount' => 1300000,
    'tax_bracket' => '0 - 60000000',
    'tax_rate' => 0.05
]
```

### **Bulk Tax Calculation**
```php
// Calculate tax for all employees in a period
$month = 7;
$year = 2025;
$taxController = new TaxController();
$result = $taxController->calculateForPayroll($month, $year);
```

---

## 🚨 **Error Handling**

### **Common Errors**
- Invalid PTKP status
- Negative taxable income
- Missing payroll data
- Duplicate tax calculations

### **Validation Rules**
- Taxable income must be positive
- PTKP status must be valid
- Tax period must be unique per employee
- Status must be valid enum value

---

## 📞 **Support & Maintenance**

### **Regular Tasks**
- Update tax brackets annually
- Update PTKP amounts
- Backup tax data
- Monitor calculation accuracy

### **Troubleshooting**
- Check employee PTKP status
- Verify payroll data integrity
- Review calculation logs
- Validate tax bracket configuration

---

## 🔮 **Future Enhancements**

### **Planned Features**
- BPJS integration
- Tax certificate generation
- Advanced tax reports
- Tax payment tracking
- Integration with government portals

### **Technical Improvements**
- Real-time tax calculation
- Advanced analytics dashboard
- Mobile tax app
- API for external systems

---

**Last Updated:** July 31, 2025  
**Version:** 1.0  
**Status:** ✅ Complete 