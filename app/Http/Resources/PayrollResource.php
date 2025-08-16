<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'company_id' => $this->company_id,
            'period' => $this->period,
            'basic_salary' => $this->basic_salary,
            'allowance' => $this->allowance,
            'overtime' => $this->overtime,
            'bonus' => $this->bonus,
            'deduction' => $this->deduction,
            'tax_amount' => $this->tax_amount,
            'bpjs_amount' => $this->bpjs_amount,
            'total_salary' => $this->total_salary,
            'status' => $this->status,
            'notes' => $this->notes,
            'generated_by' => $this->generated_by,
            'generated_at' => $this->generated_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Formatted values
            'formatted_basic_salary' => 'Rp ' . number_format($this->basic_salary, 0, ',', '.'),
            'formatted_total_salary' => 'Rp ' . number_format($this->total_salary, 0, ',', '.'),
            'formatted_period' => $this->getFormattedPeriod(),
            'status_badge' => $this->getStatusBadge(),
            
            // Relations
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->name,
                    'employee_id' => $this->employee->employee_id,
                    'department' => $this->employee->department,
                    'position' => $this->employee->position,
                ];
            }),
            'generated_by_user' => $this->whenLoaded('generatedBy', function () {
                return [
                    'id' => $this->generatedBy->id,
                    'name' => $this->generatedBy->name,
                    'email' => $this->generatedBy->email,
                ];
            }),
        ];
    }

    /**
     * Get formatted period
     */
    private function getFormattedPeriod(): string
    {
        if (!$this->period) {
            return '-';
        }

        $parts = explode('-', $this->period);
        if (count($parts) !== 2) {
            return $this->period;
        }

        $year = $parts[0];
        $month = (int) $parts[1];

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return $monthNames[$month] . ' ' . $year;
    }

    /**
     * Get status badge
     */
    private function getStatusBadge(): string
    {
        $statusClass = [
            'draft' => 'badge badge-warning',
            'approved' => 'badge badge-success',
            'paid' => 'badge badge-info',
            'rejected' => 'badge badge-danger'
        ];
        
        $statusText = [
            'draft' => 'Draft',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'rejected' => 'Rejected'
        ];
        
        $class = $statusClass[$this->status] ?? 'badge badge-secondary';
        $text = $statusText[$this->status] ?? ucfirst($this->status);
        
        return '<span class="' . $class . '">' . $text . '</span>';
    }
}
