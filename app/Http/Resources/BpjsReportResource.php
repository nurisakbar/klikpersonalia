<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BpjsReportResource extends JsonResource
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
                'name' => $this->employee->name ?? 'N/A',
                'employee_id' => $this->employee->employee_id ?? 'N/A',
                'department' => $this->employee->department ?? 'N/A',
                'position' => $this->employee->position ?? 'N/A',
            ],
            'bpjs_type' => $this->bpjs_type,
            'bpjs_type_label' => ucfirst($this->bpjs_type),
            'bpjs_type_badge' => $this->getBpjsTypeBadge(),
            'base_salary' => $this->base_salary,
            'base_salary_formatted' => 'Rp ' . number_format($this->base_salary, 0, ',', '.'),
            'employee_contribution' => $this->employee_contribution,
            'employee_contribution_formatted' => 'Rp ' . number_format($this->employee_contribution, 0, ',', '.'),
            'company_contribution' => $this->company_contribution,
            'company_contribution_formatted' => 'Rp ' . number_format($this->company_contribution, 0, ',', '.'),
            'total_contribution' => $this->total_contribution,
            'total_contribution_formatted' => 'Rp ' . number_format($this->total_contribution, 0, ',', '.'),
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'status_badge' => $this->getStatusBadge(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'actions' => $this->getActions(),
        ];
    }

    /**
     * Get BPJS type badge HTML
     */
    private function getBpjsTypeBadge(): string
    {
        if ($this->bpjs_type === 'kesehatan') {
            return '<span class="badge badge-info"><i class="fas fa-heartbeat"></i> Kesehatan</span>';
        } else {
            return '<span class="badge badge-success"><i class="fas fa-briefcase"></i> Ketenagakerjaan</span>';
        }
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge(): string
    {
        switch ($this->status) {
            case 'pending':
                return '<span class="badge badge-warning">Pending</span>';
            case 'calculated':
                return '<span class="badge badge-info">Calculated</span>';
            case 'paid':
                return '<span class="badge badge-success">Paid</span>';
            case 'verified':
                return '<span class="badge badge-primary">Verified</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }

    /**
     * Get actions HTML
     */
    private function getActions(): string
    {
        $actions = [];
        
        // View action
        $actions[] = '<a href="' . route('bpjs.show', $this->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
        
        // Edit action (if user has permission)
        if (auth()->user()->can('update', $this->resource)) {
            $actions[] = '<a href="' . route('bpjs.edit', $this->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        
        // Delete action (if user has permission)
        if (auth()->user()->can('delete', $this->resource)) {
            $actions[] = '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $this->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
        }
        
        return implode(' ', $actions);
    }
}
