<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee->id ?? null,
                'name' => $this->employee->name ?? '-',
                'employee_id' => $this->employee->employee_id ?? '-',
                'position' => $this->employee->position ?? '-',
                'department' => $this->employee->department ?? '-',
            ],
            'tax_period' => $this->tax_period ?? '-',
            'tax_period_formatted' => $this->tax_period_formatted,
            'taxable_income' => $this->taxable_income ?? 0,
            'taxable_income_formatted' => $this->taxable_income_formatted,
            'ptkp_status' => $this->ptkp_status ?? '-',
            'ptkp_amount' => $this->ptkp_amount ?? 0,
            'ptkp_amount_formatted' => $this->ptkp_amount_formatted,
            'taxable_base' => $this->taxable_base ?? 0,
            'taxable_base_formatted' => $this->taxable_base_formatted,
            'tax_amount' => $this->tax_amount ?? 0,
            'tax_amount_formatted' => $this->tax_amount_formatted,
            'tax_rate' => $this->tax_rate ?? 0,
            'tax_rate_formatted' => $this->tax_rate_formatted,
            'tax_bracket' => $this->tax_bracket ?? '-',
            'status' => $this->status ?? 'pending',
            'status_badge' => $this->status_badge,
            'notes' => $this->notes ?? null,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'created_at_formatted' => $this->created_at_formatted,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'updated_at_formatted' => $this->updated_at_formatted,
            'payroll' => $this->when($this->payroll, function() {
                return [
                    'id' => $this->payroll->id ?? null,
                    'basic_salary' => $this->payroll->basic_salary ?? 0,
                    'basic_salary_formatted' => number_format($this->payroll->basic_salary ?? 0, 0, ',', '.'),
                    'allowances' => $this->payroll->allowances ?? 0,
                    'allowances_formatted' => number_format($this->payroll->allowances ?? 0, 0, ',', '.'),
                    'overtime' => $this->payroll->overtime ?? 0,
                    'overtime_formatted' => number_format($this->payroll->overtime ?? 0, 0, ',', '.'),
                    'bonus' => $this->payroll->bonus ?? 0,
                    'bonus_formatted' => number_format($this->payroll->bonus ?? 0, 0, ',', '.'),
                    'total_income' => ($this->payroll->basic_salary ?? 0) + ($this->payroll->allowances ?? 0) + ($this->payroll->overtime ?? 0) + ($this->payroll->bonus ?? 0),
                    'total_income_formatted' => number_format(($this->payroll->basic_salary ?? 0) + ($this->payroll->allowances ?? 0) + ($this->payroll->overtime ?? 0) + ($this->payroll->bonus ?? 0), 0, ',', '.'),
                ];
            }),
            'actions' => [
                'view_url' => route('taxes.show', $this->id),
                'edit_url' => route('taxes.edit', $this->id),
            ]
        ];
    }
}
