<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BpjsResource extends JsonResource
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
            'company_id' => $this->company_id,
            'employee_id' => $this->employee_id,
            'payroll_id' => $this->payroll_id,
            'bpjs_period' => $this->bpjs_period,
            'bpjs_type' => $this->bpjs_type,
            'employee_contribution' => $this->employee_contribution,
            'company_contribution' => $this->company_contribution,
            'total_contribution' => $this->total_contribution,
            'base_salary' => $this->base_salary,
            'contribution_rate_employee' => $this->contribution_rate_employee,
            'contribution_rate_company' => $this->contribution_rate_company,
            'status' => $this->status,
            'payment_date' => $this->payment_date,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Formatted values
            'bpjs_type_text' => $this->bpjs_type === 'kesehatan' ? 'BPJS Kesehatan' : 'BPJS Ketenagakerjaan',
            'period_formatted' => \Carbon\Carbon::parse($this->bpjs_period)->format('F Y'),
            'base_salary_formatted' => 'Rp ' . number_format($this->base_salary, 0, ',', '.'),
            'employee_contribution_formatted' => 'Rp ' . number_format($this->employee_contribution, 0, ',', '.'),
            'company_contribution_formatted' => 'Rp ' . number_format($this->company_contribution, 0, ',', '.'),
            'total_contribution_formatted' => 'Rp ' . number_format($this->total_contribution, 0, ',', '.'),
            'payment_date_formatted' => $this->payment_date ? \Carbon\Carbon::parse($this->payment_date)->format('d/m/Y') : null,
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at->format('d/m/Y H:i'),
            
            // Status badge
            'status_badge' => $this->getStatusBadge(),
            
            // Relationships
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'employee_id' => $this->employee->employee_id,
                    'name' => $this->employee->name,
                    'email' => $this->employee->email,
                    'department' => $this->employee->department,
                    'position' => $this->employee->position,
                    'photo' => $this->employee->photo ?? null,
                    'bpjs_kesehatan_active' => $this->employee->bpjs_kesehatan_active ?? false,
                    'bpjs_ketenagakerjaan_active' => $this->employee->bpjs_ketenagakerjaan_active ?? false,
                    'bpjs_kesehatan_number' => $this->employee->bpjs_kesehatan_number ?? null,
                    'bpjs_ketenagakerjaan_number' => $this->employee->bpjs_ketenagakerjaan_number ?? null,
                ];
            }),
            
            'payroll' => $this->whenLoaded('payroll', function () {
                return [
                    'id' => $this->payroll->id,
                    'payroll_period' => $this->payroll->payroll_period,
                    'basic_salary' => $this->payroll->basic_salary,
                    'total_earnings' => $this->payroll->total_earnings,
                    'total_deductions' => $this->payroll->total_deductions,
                    'net_salary' => $this->payroll->net_salary,
                ];
            }),
            
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
        ];
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(): string
    {
        $statusClass = [
            'pending' => 'badge badge-warning',
            'calculated' => 'badge badge-info',
            'paid' => 'badge badge-success',
            'verified' => 'badge badge-primary'
        ];
        
        $statusText = [
            'pending' => 'Pending',
            'calculated' => 'Calculated',
            'paid' => 'Paid',
            'verified' => 'Verified'
        ];
        
        return '<span class="' . $statusClass[$this->status] . '">' . $statusText[$this->status] . '</span>';
    }
}
