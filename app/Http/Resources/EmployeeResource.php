<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'join_date' => $this->join_date?->format('Y-m-d'),
            'join_date_formatted' => $this->join_date?->format('d/m/Y'),
            'department' => $this->department,
            'position' => $this->position,
            'basic_salary' => $this->basic_salary,
            'salary_formatted' => 'Rp ' . number_format($this->basic_salary, 0, ',', '.'),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_badge' => $this->getStatusBadge(),
            'emergency_contact' => $this->emergency_contact,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relations
            'company' => $this->whenLoaded('company'),
            'user' => $this->whenLoaded('user'),
            
            // Additional computed fields
            'years_of_service' => $this->getYearsOfService(),
            'is_active' => $this->status === 'active',
        ];
    }

    /**
     * Get the status label.
     */
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'terminated' => 'Berhenti',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get the status badge HTML.
     */
    private function getStatusBadge(): string
    {
        $statusClass = match($this->status) {
            'active' => 'badge badge-success',
            'inactive' => 'badge badge-warning',
            'terminated' => 'badge badge-danger',
            default => 'badge badge-secondary'
        };
        
        return '<span class="' . $statusClass . '">' . $this->getStatusLabel() . '</span>';
    }

    /**
     * Get years of service.
     */
    private function getYearsOfService(): ?float
    {
        if (!$this->join_date) {
            return null;
        }

        $now = now();
        $joinDate = $this->join_date;
        
        return round($joinDate->diffInYears($now, true), 1);
    }

    /**
     * Get additional data when transforming for DataTables.
     */
    public function withDataTableActions(): array
    {
        $data = $this->toArray(request());
        
        $data['action'] = $this->getActionButtons();
        
        return $data;
    }

    /**
     * Get action buttons for DataTables.
     */
    private function getActionButtons(): string
    {
        return '
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $this->id . '" title="Detail">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="' . $this->id . '" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' . $this->id . '" data-name="' . htmlspecialchars($this->name) . '" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        ';
    }
}
