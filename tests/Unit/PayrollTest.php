<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Company;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\Leave;
use App\Models\Tax;
use App\Models\Bpjs;
use App\Models\EmployeeBenefit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class PayrollTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $company;
    protected $user;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin'
        ]);
        $this->employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'salary' => 5000000
        ]);
    }

    /** @test */
    public function it_can_create_a_payroll()
    {
        $payrollData = [
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_period' => '2024-01',
            'basic_salary' => 5000000,
            'allowances' => 500000,
            'deductions' => 200000,
            'overtime_pay' => 100000,
            'gross_salary' => 5600000,
            'net_salary' => 5400000,
            'status' => 'pending',
            'payment_date' => '2024-01-25'
        ];

        $payroll = Payroll::create($payrollData);

        $this->assertInstanceOf(Payroll::class, $payroll);
        $this->assertEquals('2024-01', $payroll->payroll_period);
        $this->assertEquals(5000000, $payroll->basic_salary);
        $this->assertEquals(5600000, $payroll->gross_salary);
        $this->assertEquals(5400000, $payroll->net_salary);
        $this->assertEquals('pending', $payroll->status);
    }

    /** @test */
    public function it_belongs_to_employee()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        $this->assertInstanceOf(Employee::class, $payroll->employee);
        $this->assertEquals($this->employee->id, $payroll->employee->id);
    }

    /** @test */
    public function it_belongs_to_company()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        $this->assertInstanceOf(Company::class, $payroll->company);
        $this->assertEquals($this->company->id, $payroll->company->id);
    }

    /** @test */
    public function it_has_many_attendances()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Attendance::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(3, $payroll->attendances->count());
        $this->assertInstanceOf(Attendance::class, $payroll->attendances->first());
    }

    /** @test */
    public function it_has_many_overtimes()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Overtime::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(2, $payroll->overtimes->count());
        $this->assertInstanceOf(Overtime::class, $payroll->overtimes->first());
    }

    /** @test */
    public function it_has_many_leaves()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Leave::factory()->count(1)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(1, $payroll->leaves->count());
        $this->assertInstanceOf(Leave::class, $payroll->leaves->first());
    }

    /** @test */
    public function it_has_many_taxes()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Tax::factory()->count(1)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(1, $payroll->taxes->count());
        $this->assertInstanceOf(Tax::class, $payroll->taxes->first());
    }

    /** @test */
    public function it_has_many_bpjs()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Bpjs::factory()->count(1)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(1, $payroll->bpjs->count());
        $this->assertInstanceOf(Bpjs::class, $payroll->bpjs->first());
    }

    /** @test */
    public function it_has_many_employee_benefits()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        EmployeeBenefit::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertEquals(2, $payroll->employeeBenefits->count());
        $this->assertInstanceOf(EmployeeBenefit::class, $payroll->employeeBenefits->first());
    }

    /** @test */
    public function it_can_scope_by_period()
    {
        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_period' => '2024-01'
        ]);

        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_period' => '2024-02'
        ]);

        $januaryPayrolls = Payroll::byPeriod('2024-01')->get();

        $this->assertEquals(1, $januaryPayrolls->count());
        $this->assertEquals('2024-01', $januaryPayrolls->first()->payroll_period);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $pendingPayrolls = Payroll::byStatus('pending')->get();

        $this->assertEquals(1, $pendingPayrolls->count());
        $this->assertEquals('pending', $pendingPayrolls->first()->status);
    }

    /** @test */
    public function it_can_scope_by_employee()
    {
        $employee2 = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee2->id
        ]);

        $employeePayrolls = Payroll::byEmployee($this->employee->id)->get();

        $this->assertEquals(1, $employeePayrolls->count());
        $this->assertEquals($this->employee->id, $employeePayrolls->first()->employee_id);
    }

    /** @test */
    public function it_can_get_status_badge()
    {
        $pendingPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertEquals('warning', $pendingPayroll->status_badge);
        $this->assertEquals('success', $paidPayroll->status_badge);
    }

    /** @test */
    public function it_can_get_status_label()
    {
        $pendingPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertEquals('Pending', $pendingPayroll->status_label);
        $this->assertEquals('Paid', $paidPayroll->status_label);
    }

    /** @test */
    public function it_can_calculate_gross_salary()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'basic_salary' => 5000000,
            'allowances' => 500000,
            'overtime_pay' => 100000
        ]);

        $expectedGross = 5000000 + 500000 + 100000;
        $this->assertEquals($expectedGross, $payroll->calculateGrossSalary());
    }

    /** @test */
    public function it_can_calculate_net_salary()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'basic_salary' => 5000000,
            'allowances' => 500000,
            'overtime_pay' => 100000,
            'deductions' => 200000
        ]);

        $grossSalary = 5000000 + 500000 + 100000;
        $expectedNet = $grossSalary - 200000;
        $this->assertEquals($expectedNet, $payroll->calculateNetSalary());
    }

    /** @test */
    public function it_can_get_formatted_amounts()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'basic_salary' => 5000000,
            'gross_salary' => 5600000,
            'net_salary' => 5400000
        ]);

        $this->assertEquals('Rp 5,000,000', $payroll->formatted_basic_salary);
        $this->assertEquals('Rp 5,600,000', $payroll->formatted_gross_salary);
        $this->assertEquals('Rp 5,400,000', $payroll->formatted_net_salary);
    }

    /** @test */
    public function it_can_check_if_paid()
    {
        $pendingPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertFalse($pendingPayroll->isPaid());
        $this->assertTrue($paidPayroll->isPaid());
    }

    /** @test */
    public function it_can_check_if_pending()
    {
        $pendingPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidPayroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertTrue($pendingPayroll->isPending());
        $this->assertFalse($paidPayroll->isPending());
    }

    /** @test */
    public function it_can_get_total_attendance_hours()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'total_hours' => 8
        ]);

        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'total_hours' => 7.5
        ]);

        $this->assertEquals(15.5, $payroll->total_attendance_hours);
    }

    /** @test */
    public function it_can_get_total_overtime_hours()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Overtime::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'hours' => 2
        ]);

        Overtime::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'hours' => 1.5
        ]);

        $this->assertEquals(3.5, $payroll->total_overtime_hours);
    }

    /** @test */
    public function it_can_get_total_leave_days()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Leave::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'days' => 3
        ]);

        Leave::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'days' => 2
        ]);

        $this->assertEquals(5, $payroll->total_leave_days);
    }

    /** @test */
    public function it_can_get_total_tax_amount()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'tax_amount' => 100000
        ]);

        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'tax_amount' => 50000
        ]);

        $this->assertEquals(150000, $payroll->total_tax_amount);
    }

    /** @test */
    public function it_can_get_total_bpjs_amount()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        Bpjs::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'total_amount' => 200000
        ]);

        Bpjs::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id,
            'total_amount' => 150000
        ]);

        $this->assertEquals(350000, $payroll->total_bpjs_amount);
    }
} 