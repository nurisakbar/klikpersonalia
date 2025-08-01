<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Company;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Tax;
use App\Models\Bpjs;
use App\Models\EmployeeBenefit;
use App\Models\Performance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function it_can_create_an_employee()
    {
        $employeeData = [
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '081234567890',
            'position' => 'Software Engineer',
            'department' => 'IT',
            'salary' => 5000000,
            'hire_date' => '2023-01-15',
            'is_active' => true
        ];

        $employee = Employee::create($employeeData);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('John', $employee->first_name);
        $this->assertEquals('Doe', $employee->last_name);
        $this->assertEquals('john.doe@example.com', $employee->email);
        $this->assertEquals('Software Engineer', $employee->position);
        $this->assertEquals(5000000, $employee->salary);
        $this->assertTrue($employee->is_active);
    }

    /** @test */
    public function it_has_full_name_attribute()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $this->assertEquals('John Doe', $employee->full_name);
    }

    /** @test */
    public function it_has_avatar_url_attribute()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'avatar' => 'avatars/employee.jpg'
        ]);

        $this->assertStringContainsString('avatars/employee.jpg', $employee->avatar_url);
    }

    /** @test */
    public function it_has_employment_duration_attribute()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'hire_date' => '2023-01-15'
        ]);

        $this->assertIsString($employee->employment_duration);
        $this->assertStringContainsString('year', $employee->employment_duration);
    }

    /** @test */
    public function it_can_scope_active_employees()
    {
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);
        
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);

        $activeEmployees = Employee::active()->get();

        $this->assertEquals(1, $activeEmployees->count());
        $this->assertTrue($activeEmployees->first()->is_active);
    }

    /** @test */
    public function it_can_scope_by_department()
    {
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'department' => 'IT'
        ]);
        
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'department' => 'HR'
        ]);

        $itEmployees = Employee::byDepartment('IT')->get();

        $this->assertEquals(1, $itEmployees->count());
        $this->assertEquals('IT', $itEmployees->first()->department);
    }

    /** @test */
    public function it_can_scope_by_position()
    {
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'position' => 'Software Engineer'
        ]);
        
        Employee::factory()->create([
            'company_id' => $this->company->id,
            'position' => 'HR Manager'
        ]);

        $engineers = Employee::byPosition('Software Engineer')->get();

        $this->assertEquals(1, $engineers->count());
        $this->assertEquals('Software Engineer', $engineers->first()->position);
    }

    /** @test */
    public function it_belongs_to_company()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        $this->assertInstanceOf(Company::class, $employee->company);
        $this->assertEquals($this->company->id, $employee->company->id);
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $employee->user);
        $this->assertEquals($this->user->id, $employee->user->id);
    }

    /** @test */
    public function it_has_many_payrolls()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Payroll::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(3, $employee->payrolls->count());
        $this->assertInstanceOf(Payroll::class, $employee->payrolls->first());
    }

    /** @test */
    public function it_has_many_attendances()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Attendance::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(5, $employee->attendances->count());
        $this->assertInstanceOf(Attendance::class, $employee->attendances->first());
    }

    /** @test */
    public function it_has_many_leaves()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Leave::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(2, $employee->leaves->count());
        $this->assertInstanceOf(Leave::class, $employee->leaves->first());
    }

    /** @test */
    public function it_has_many_overtimes()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Overtime::factory()->count(4)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(4, $employee->overtimes->count());
        $this->assertInstanceOf(Overtime::class, $employee->overtimes->first());
    }

    /** @test */
    public function it_has_many_taxes()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Tax::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(3, $employee->taxes->count());
        $this->assertInstanceOf(Tax::class, $employee->taxes->first());
    }

    /** @test */
    public function it_has_many_bpjs()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Bpjs::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(2, $employee->bpjs->count());
        $this->assertInstanceOf(Bpjs::class, $employee->bpjs->first());
    }

    /** @test */
    public function it_has_many_employee_benefits()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        EmployeeBenefit::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(3, $employee->employeeBenefits->count());
        $this->assertInstanceOf(EmployeeBenefit::class, $employee->employeeBenefits->first());
    }

    /** @test */
    public function it_has_many_performances()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Performance::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id
        ]);

        $this->assertEquals(2, $employee->performances->count());
        $this->assertInstanceOf(Performance::class, $employee->performances->first());
    }

    /** @test */
    public function it_can_calculate_total_attendance_hours()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'total_hours' => 8
        ]);

        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'total_hours' => 7.5
        ]);

        $this->assertEquals(15.5, $employee->total_attendance_hours);
    }

    /** @test */
    public function it_can_calculate_total_leave_days()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Leave::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'days' => 3
        ]);

        Leave::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'days' => 2
        ]);

        $this->assertEquals(5, $employee->total_leave_days);
    }

    /** @test */
    public function it_can_calculate_total_overtime_hours()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id
        ]);

        Overtime::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'hours' => 2
        ]);

        Overtime::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
            'hours' => 1.5
        ]);

        $this->assertEquals(3.5, $employee->total_overtime_hours);
    }

    /** @test */
    public function it_can_check_if_employee_is_active()
    {
        $activeEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);

        $inactiveEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);

        $this->assertTrue($activeEmployee->isActive());
        $this->assertFalse($inactiveEmployee->isActive());
    }

    /** @test */
    public function it_can_get_employment_status_label()
    {
        $activeEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);

        $inactiveEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);

        $this->assertEquals('Active', $activeEmployee->employment_status_label);
        $this->assertEquals('Inactive', $inactiveEmployee->employment_status_label);
    }

    /** @test */
    public function it_can_get_employment_status_badge()
    {
        $activeEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true
        ]);

        $inactiveEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false
        ]);

        $this->assertEquals('success', $activeEmployee->employment_status_badge);
        $this->assertEquals('secondary', $inactiveEmployee->employment_status_badge);
    }
} 