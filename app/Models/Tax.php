<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Tax extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'payroll_id',
        'tax_period',
        'taxable_income',
        'ptkp_status',
        'ptkp_amount',
        'taxable_base',
        'tax_amount',
        'tax_bracket',
        'tax_rate',
        'status',
        'notes'
    ];

    protected $casts = [
        'taxable_income' => 'decimal:2',
        'ptkp_amount' => 'decimal:2',
        'taxable_base' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    // PTKP Status constants
    const PTKP_STATUSES = [
        'TK/0' => 'Single, no dependents',
        'TK/1' => 'Single, 1 dependent',
        'TK/2' => 'Single, 2 dependents',
        'TK/3' => 'Single, 3 dependents',
        'K/0' => 'Married, no dependents',
        'K/1' => 'Married, 1 dependent',
        'K/2' => 'Married, 2 dependents',
        'K/3' => 'Married, 3 dependents',
    ];

    // PTKP Amounts (2024)
    const PTKP_AMOUNTS = [
        'TK/0' => 54000000,
        'TK/1' => 58500000,
        'TK/2' => 63000000,
        'TK/3' => 67500000,
        'K/0' => 58500000,
        'K/1' => 63000000,
        'K/2' => 67500000,
        'K/3' => 72000000,
    ];

    // Tax Brackets (2024)
    const TAX_BRACKETS = [
        ['min' => 0, 'max' => 60000000, 'rate' => 0.05],
        ['min' => 60000000, 'max' => 250000000, 'rate' => 0.15],
        ['min' => 250000000, 'max' => 500000000, 'rate' => 0.25],
        ['min' => 500000000, 'max' => 5000000000, 'rate' => 0.30],
        ['min' => 5000000000, 'max' => null, 'rate' => 0.35],
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CALCULATED = 'calculated';
    const STATUS_PAID = 'paid';
    const STATUS_VERIFIED = 'verified';

    /**
     * Calculate PPh 21 for an employee
     */
    public static function calculatePPh21($employee, $taxableIncome, $period = null)
    {
        // Get PTKP status from employee
        $ptkpStatus = $employee->ptkp_status ?? 'TK/0';
        $ptkpAmount = self::PTKP_AMOUNTS[$ptkpStatus] ?? self::PTKP_AMOUNTS['TK/0'];

        // Calculate taxable base
        $taxableBase = max(0, $taxableIncome - $ptkpAmount);

        // Calculate tax amount
        $taxAmount = 0;
        $taxBracket = null;
        $taxRate = 0;

        foreach (self::TAX_BRACKETS as $bracket) {
            if ($taxableBase > $bracket['min']) {
                $bracketMax = $bracket['max'] ?? PHP_INT_MAX;
                $bracketAmount = min($taxableBase, $bracketMax) - $bracket['min'];
                $taxAmount += $bracketAmount * $bracket['rate'];
                
                if ($taxableBase <= $bracketMax) {
                    $taxBracket = $bracket['min'] . ' - ' . ($bracket['max'] ?? '∞');
                    $taxRate = $bracket['rate'];
                    break;
                }
            }
        }

        return [
            'ptkp_status' => $ptkpStatus,
            'ptkp_amount' => $ptkpAmount,
            'taxable_base' => $taxableBase,
            'tax_amount' => $taxAmount,
            'tax_bracket' => $taxBracket,
            'tax_rate' => $taxRate,
        ];
    }

    /**
     * Get tax bracket for given income
     */
    public static function getTaxBracket($taxableBase)
    {
        foreach (self::TAX_BRACKETS as $bracket) {
            if ($taxableBase > $bracket['min'] && ($bracket['max'] === null || $taxableBase <= $bracket['max'])) {
                return $bracket;
            }
        }
        return null;
    }

    /**
     * Get PTKP amount for status
     */
    public static function getPtkpAmount($status)
    {
        return self::PTKP_AMOUNTS[$status] ?? self::PTKP_AMOUNTS['TK/0'];
    }

    // Accessors for formatted data
    public function getTaxPeriodFormattedAttribute()
    {
        if (!$this->tax_period) {
            return '-';
        }
        
        try {
            return \Carbon\Carbon::createFromFormat('Y-m', $this->tax_period)->format('F Y');
        } catch (\Exception $e) {
            return $this->tax_period;
        }
    }

    public function getTaxableIncomeFormattedAttribute()
    {
        return 'Rp ' . number_format($this->taxable_income ?? 0, 0, ',', '.');
    }

    public function getPtkpAmountFormattedAttribute()
    {
        return 'Rp ' . number_format($this->ptkp_amount ?? 0, 0, ',', '.');
    }

    public function getTaxableBaseFormattedAttribute()
    {
        return 'Rp ' . number_format($this->taxable_base ?? 0, 0, ',', '.');
    }

    public function getTaxAmountFormattedAttribute()
    {
        return 'Rp ' . number_format($this->tax_amount ?? 0, 0, ',', '.');
    }

    public function getTaxRateFormattedAttribute()
    {
        return number_format(($this->tax_rate ?? 0) * 100, 1) . '%';
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : '-';
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return '<span class="badge badge-secondary">Menunggu</span>';
            case 'calculated':
                return '<span class="badge badge-info">Dihitung</span>';
            case 'paid':
                return '<span class="badge badge-success">Dibayar</span>';
            case 'verified':
                return '<span class="badge badge-primary">Terverifikasi</span>';
            default:
                return '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>';
        }
    }
} 