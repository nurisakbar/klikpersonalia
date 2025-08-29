<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'default_value' => $this->default_value,
            'default_value_formatted' => 'Rp ' . number_format($this->default_value, 0, ',', '.'),
            'formatted_value' => 'Rp ' . number_format($this->default_value, 0, ',', '.'),
            'type' => $this->type,
            'type_text' => $this->type_text,
            'is_active' => $this->is_active,
            'is_taxable' => $this->is_taxable,
            'is_bpjs_calculated' => $this->is_bpjs_calculated,
            'sort_order' => $this->sort_order,
            'status_text' => $this->status_text,
            'company_id' => $this->company_id,
            'company_name' => $this->company->name ?? null,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
            'is_taxable_badge' => $this->resource->is_taxable ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>',
            'is_bpjs_calculated_badge' => $this->resource->is_bpjs_calculated ? '<span class="badge badge-info">Ya</span>' : '<span class="badge badge-secondary">Tidak</span>',
            'action' => $this->resource->getActionButtons(),
            'employee_count' => $this->resource->employee_count ?? 0,
            'is_earning' => $this->resource->isEarning(),
            'is_deduction' => $this->resource->isDeduction(),
            'can_be_deleted' => $this->resource->can_be_deleted,
            'usage_stats' => [
                'total_usage' => 0, // Placeholder for future implementation
                'employee_count' => $this->resource->employee_count ?? 0,
                'last_period' => '-', // Placeholder for future implementation
                'average_value' => 0 // Placeholder for future implementation
            ]
        ];
    }
}
