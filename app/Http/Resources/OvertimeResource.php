<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class OvertimeResource extends JsonResource
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
            'overtime_type' => $this->overtime_type,
            'overtime_type_label' => $this->getOvertimeTypeLabel(),
            'date' => $this->date,
            'formatted_date' => $this->formatted_date,
            'start_time' => $this->start_time,
            'formatted_start_time' => $this->formatted_start_time,
            'end_time' => $this->end_time,
            'formatted_end_time' => $this->formatted_end_time,
            'total_hours' => $this->total_hours,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_badge' => $this->status_badge,
            'type_badge' => $this->type_badge,
            'approval_notes' => $this->approval_notes,
            'approved_by' => $this->approver->name ?? null,
            'approved_at' => $this->approved_at,
            'formatted_approved_at' => $this->approved_at ? Carbon::parse($this->approved_at)->format('d/m/Y H:i') : null,
            'attachment_path' => $this->attachment,
            'attachment_url' => $this->attachment ? asset('storage/' . $this->attachment) : null,
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
     * Get overtime type label
     */
    private function getOvertimeTypeLabel(): string
    {
        $labels = [
            'regular' => 'Lembur Regular',
            'holiday' => 'Lembur Hari Libur',
            'weekend' => 'Lembur Akhir Pekan',
            'emergency' => 'Lembur Darurat',
        ];

        return $labels[$this->overtime_type] ?? $this->overtime_type;
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
