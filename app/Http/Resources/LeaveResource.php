<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LeaveResource extends JsonResource
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
            'employee_name' => $this->employee->name ?? null,
            'employee_id_number' => $this->employee->employee_id ?? null,
            'leave_type' => $this->leave_type,
            'leave_type_label' => $this->getLeaveTypeLabel(),
            'start_date' => $this->start_date,
            'formatted_start_date' => $this->formatted_start_date,
            'end_date' => $this->end_date,
            'formatted_end_date' => $this->formatted_end_date,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_badge' => $this->status_badge,
            'type_badge' => $this->type_badge,
            'approval_notes' => $this->approval_notes,
            'approved_by' => $this->approver->name ?? null,
            'approved_at' => $this->approved_at,
            'formatted_approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->format('d/m/Y H:i') : null,
            'attachment_path' => $this->attachment_path,
            'attachment_url' => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
            'created_at' => $this->created_at,
            'formatted_created_at' => $this->created_at->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at,
            'can_edit' => $this->status === 'pending',
            'can_delete' => $this->status === 'pending',
            'can_approve' => $this->status === 'pending' && in_array(auth()->user()->role, ['admin', 'hr', 'manager']),
            'can_reject' => $this->status === 'pending' && in_array(auth()->user()->role, ['admin', 'hr', 'manager']),
        ];
    }

    /**
     * Get leave type label
     */
    private function getLeaveTypeLabel(): string
    {
        $labels = [
            'annual' => 'Cuti Tahunan',
            'sick' => 'Cuti Sakit',
            'maternity' => 'Cuti Melahirkan',
            'paternity' => 'Cuti Melahirkan Suami',
            'other' => 'Cuti Lainnya',
        ];

        return $labels[$this->leave_type] ?? $this->leave_type;
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        $labels = [
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
