<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tax;
use App\Models\Employee;
use App\Models\Company;
use App\Models\User;
use App\Models\Payroll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaxTest extends TestCase
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
            'salary' => 5000000,
            'ptkp_status' => 'TK/0'
        ]);
    }

    /** @test */
    public function it_can_create_a_tax_record()
    {
        $taxData = [
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => null,
            'tax_period' => '2024-01',
            'gross_income' => 5000000,
            'net_income' => 4500000,
            'taxable_income' => 2000000,
            'tax_amount' => 100000,
            'tax_rate' => 5.0,
            'status' => 'pending'
        ];

        $tax = Tax::create($taxData);

        $this->assertInstanceOf(Tax::class, $tax);
        $this->assertEquals('2024-01', $tax->tax_period);
        $this->assertEquals(5000000, $tax->gross_income);
        $this->assertEquals(2000000, $tax->taxable_income);
        $this->assertEquals(100000, $tax->tax_amount);
        $this->assertEquals(5.0, $tax->tax_rate);
    }

    /** @test */
    public function it_belongs_to_employee()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        $this->assertInstanceOf(Employee::class, $tax->employee);
        $this->assertEquals($this->employee->id, $tax->employee->id);
    }

    /** @test */
    public function it_belongs_to_company()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        $this->assertInstanceOf(Company::class, $tax->company);
        $this->assertEquals($this->company->id, $tax->company->id);
    }

    /** @test */
    public function it_belongs_to_payroll()
    {
        $payroll = Payroll::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id
        ]);

        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'payroll_id' => $payroll->id
        ]);

        $this->assertInstanceOf(Payroll::class, $tax->payroll);
        $this->assertEquals($payroll->id, $tax->payroll->id);
    }

    /** @test */
    public function it_can_scope_by_period()
    {
        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'tax_period' => '2024-01'
        ]);

        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'tax_period' => '2024-02'
        ]);

        $januaryTaxes = Tax::byPeriod('2024-01')->get();

        $this->assertEquals(1, $januaryTaxes->count());
        $this->assertEquals('2024-01', $januaryTaxes->first()->tax_period);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $pendingTaxes = Tax::byStatus('pending')->get();

        $this->assertEquals(1, $pendingTaxes->count());
        $this->assertEquals('pending', $pendingTaxes->first()->status);
    }

    /** @test */
    public function it_can_get_status_badge()
    {
        $pendingTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertEquals('warning', $pendingTax->status_badge);
        $this->assertEquals('success', $paidTax->status_badge);
    }

    /** @test */
    public function it_can_get_status_label()
    {
        $pendingTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertEquals('Pending', $pendingTax->status_label);
        $this->assertEquals('Paid', $paidTax->status_label);
    }

    /** @test */
    public function it_can_get_formatted_amounts()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'gross_income' => 5000000,
            'net_income' => 4500000,
            'taxable_income' => 2000000,
            'tax_amount' => 100000
        ]);

        $this->assertEquals('Rp 5,000,000', $tax->formatted_gross_income);
        $this->assertEquals('Rp 4,500,000', $tax->formatted_net_income);
        $this->assertEquals('Rp 2,000,000', $tax->formatted_taxable_income);
        $this->assertEquals('Rp 100,000', $tax->formatted_tax_amount);
    }

    /** @test */
    public function it_can_check_if_paid()
    {
        $pendingTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertFalse($pendingTax->isPaid());
        $this->assertTrue($paidTax->isPaid());
    }

    /** @test */
    public function it_can_check_if_pending()
    {
        $pendingTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $paidTax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $this->assertTrue($pendingTax->isPending());
        $this->assertFalse($paidTax->isPending());
    }

    /** @test */
    public function it_can_calculate_ptkp_amount()
    {
        $ptkpAmount = Tax::calculatePTKP('TK/0');
        $this->assertEquals(54000000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('TK/1');
        $this->assertEquals(58500000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('TK/2');
        $this->assertEquals(63000000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('TK/3');
        $this->assertEquals(67500000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('K/0');
        $this->assertEquals(58500000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('K/1');
        $this->assertEquals(63000000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('K/2');
        $this->assertEquals(67500000, $ptkpAmount);

        $ptkpAmount = Tax::calculatePTKP('K/3');
        $this->assertEquals(72000000, $ptkpAmount);
    }

    /** @test */
    public function it_can_calculate_tax_bracket()
    {
        $bracket = Tax::calculateTaxBracket(50000000);
        $this->assertEquals(5.0, $bracket);

        $bracket = Tax::calculateTaxBracket(250000000);
        $this->assertEquals(15.0, $bracket);

        $bracket = Tax::calculateTaxBracket(500000000);
        $this->assertEquals(25.0, $bracket);

        $bracket = Tax::calculateTaxBracket(5000000000);
        $this->assertEquals(30.0, $bracket);
    }

    /** @test */
    public function it_can_calculate_pph21()
    {
        // Test case 1: Basic calculation
        $grossIncome = 5000000; // 5 million per month
        $ptkpStatus = 'TK/0';
        $allowances = 500000;
        $deductions = 200000;

        $pph21 = Tax::calculatePPh21($grossIncome, $ptkpStatus, $allowances, $deductions);

        // Expected calculation:
        // Gross income: 5,000,000
        // Allowances: 500,000
        // Deductions: 200,000
        // Net income: 5,300,000
        // Annual net income: 63,600,000
        // PTKP (TK/0): 54,000,000
        // Taxable income: 9,600,000
        // Tax (5%): 480,000
        // Monthly tax: 40,000

        $this->assertEquals(40000, $pph21);
    }

    /** @test */
    public function it_can_calculate_pph21_with_different_ptkp_status()
    {
        $grossIncome = 5000000;
        $allowances = 500000;
        $deductions = 200000;

        // TK/1 should have higher PTKP
        $pph21TK1 = Tax::calculatePPh21($grossIncome, 'TK/1', $allowances, $deductions);
        $pph21TK0 = Tax::calculatePPh21($grossIncome, 'TK/0', $allowances, $deductions);

        // TK/1 should have lower tax due to higher PTKP
        $this->assertLessThan($pph21TK0, $pph21TK1);
    }

    /** @test */
    public function it_can_calculate_pph21_with_high_income()
    {
        $grossIncome = 20000000; // 20 million per month
        $ptkpStatus = 'TK/0';
        $allowances = 1000000;
        $deductions = 500000;

        $pph21 = Tax::calculatePPh21($grossIncome, $ptkpStatus, $allowances, $deductions);

        // This should be a significant amount due to higher tax brackets
        $this->assertGreaterThan(1000000, $pph21);
    }

    /** @test */
    public function it_can_calculate_pph21_with_zero_taxable_income()
    {
        $grossIncome = 3000000; // 3 million per month
        $ptkpStatus = 'TK/0';
        $allowances = 0;
        $deductions = 0;

        $pph21 = Tax::calculatePPh21($grossIncome, $ptkpStatus, $allowances, $deductions);

        // Annual income: 36,000,000
        // PTKP: 54,000,000
        // Taxable income: 0 (negative, so 0)
        // Tax: 0
        $this->assertEquals(0, $pph21);
    }

    /** @test */
    public function it_can_calculate_pph21_with_negative_taxable_income()
    {
        $grossIncome = 2000000; // 2 million per month
        $ptkpStatus = 'TK/0';
        $allowances = 0;
        $deductions = 0;

        $pph21 = Tax::calculatePPh21($grossIncome, $ptkpStatus, $allowances, $deductions);

        // Annual income: 24,000,000
        // PTKP: 54,000,000
        // Taxable income: 0 (negative, so 0)
        // Tax: 0
        $this->assertEquals(0, $pph21);
    }

    /** @test */
    public function it_can_calculate_effective_tax_rate()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'gross_income' => 5000000,
            'tax_amount' => 100000
        ]);

        $effectiveRate = $tax->calculateEffectiveTaxRate();
        $expectedRate = (100000 / 5000000) * 100;

        $this->assertEquals($expectedRate, $effectiveRate);
    }

    /** @test */
    public function it_can_get_tax_percentage()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'gross_income' => 5000000,
            'tax_amount' => 100000
        ]);

        $percentage = $tax->tax_percentage;
        $expectedPercentage = (100000 / 5000000) * 100;

        $this->assertEquals($expectedPercentage, $percentage);
    }

    /** @test */
    public function it_can_get_annual_taxable_income()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'taxable_income' => 2000000
        ]);

        $annualTaxableIncome = $tax->annual_taxable_income;
        $expectedAnnual = 2000000 * 12;

        $this->assertEquals($expectedAnnual, $annualTaxableIncome);
    }

    /** @test */
    public function it_can_get_annual_tax_amount()
    {
        $tax = Tax::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $this->employee->id,
            'tax_amount' => 100000
        ]);

        $annualTaxAmount = $tax->annual_tax_amount;
        $expectedAnnual = 100000 * 12;

        $this->assertEquals($expectedAnnual, $annualTaxAmount);
    }
} 