<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailTaxResource extends JsonResource
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
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'employee_id' => $this->employee->employee_id,
                'position' => $this->employee->position,
                'department' => $this->employee->department,
            ],
            'tax_period' => $this->tax_period,
            'tax_period_formatted' => $this->tax_period ? \Carbon\Carbon::createFromFormat('Y-m', $this->tax_period)->format('F Y') : '-',
            'taxable_income' => $this->taxable_income,
            'taxable_income_formatted' => 'Rp ' . number_format($this->taxable_income ?? 0, 0, ',', '.'),
            'ptkp_status' => $this->ptkp_status,
            'ptkp_amount' => $this->ptkp_amount,
            'ptkp_amount_formatted' => 'Rp ' . number_format($this->ptkp_amount ?? 0, 0, ',', '.'),
            'taxable_base' => $this->taxable_base,
            'taxable_base_formatted' => 'Rp ' . number_format($this->taxable_base ?? 0, 0, ',', '.'),
            'tax_amount' => $this->tax_amount,
            'tax_amount_formatted' => 'Rp ' . number_format($this->tax_amount ?? 0, 0, ',', '.'),
            'tax_bracket' => $this->tax_bracket,
            'tax_rate' => $this->tax_rate,
            'tax_rate_formatted' => number_format(($this->tax_rate ?? 0) * 100, 1) . '%',
            'status' => $this->status,
            'status_badge' => $this->getStatusBadge(),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : '-',
            'updated_at' => $this->updated_at,
            'updated_at_formatted' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : '-',
        ];
    }

    private function getStatusBadge(): string
    {
        $status = $this->status ?? 'pending';
        $statusClass = [
            'pending' => 'badge badge-secondary',
            'calculated' => 'badge badge-info',
            'paid' => 'badge badge-success',
            'verified' => 'badge badge-primary'
        ];
        
        $statusText = [
            'pending' => 'Menunggu',
            'calculated' => 'Dihitung',
            'paid' => 'Dibayar',
            'verified' => 'Terverifikasi'
        ];
        
        $class = $statusClass[$status] ?? 'badge badge-secondary';
        $text = $statusText[$status] ?? ucfirst($status);
        
        return '<span class="' . $class . '">' . $text . '</span>';
    }
}
