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
            'formatted_default_value' => $this->formatted_default_value,
            'type' => $this->type,
            'type_text' => $this->type_text,
            'is_active' => $this->is_active,
            'status_text' => $this->status_text,
            'is_taxable' => $this->is_taxable,
            'is_bpjs_calculated' => $this->is_bpjs_calculated,
            'sort_order' => $this->sort_order,
            'company_id' => $this->company_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'company' => new CompanyResource($this->whenLoaded('company')),
        ];
    }
}
