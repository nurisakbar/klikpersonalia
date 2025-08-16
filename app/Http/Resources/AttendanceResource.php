<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceResource extends JsonResource
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
            'employee_department' => $this->employee->department ?? null,
            'date' => $this->date->format('Y-m-d'),
            'date_formatted' => $this->date->format('d/m/Y'),
            'check_in' => $this->check_in ? Carbon::parse($this->check_in)->format('H:i') : null,
            'check_out' => $this->check_out ? Carbon::parse($this->check_out)->format('H:i') : null,
            'total_hours' => $this->total_hours,
            'total_hours_formatted' => $this->total_hours ? $this->total_hours . ' jam' : null,
            'overtime_hours' => $this->overtime_hours,
            'overtime_hours_formatted' => $this->overtime_hours ? $this->overtime_hours . ' jam' : null,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'status_badge_class' => $this->getStatusBadgeClass(),
            'notes' => $this->notes,
            'check_in_location' => $this->check_in_location,
            'check_out_location' => $this->check_out_location,
            'check_in_ip' => $this->check_in_ip,
            'check_out_ip' => $this->check_out_ip,
            'check_in_device' => $this->check_in_device,
            'check_out_device' => $this->check_out_device,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get status text in Indonesian
     */
    private function getStatusText(): string
    {
        $statusTexts = [
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'half_day' => 'Setengah Hari',
            'leave' => 'Cuti',
            'holiday' => 'Libur'
        ];

        return $statusTexts[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class
     */
    private function getStatusBadgeClass(): string
    {
        $statusClasses = [
            'present' => 'badge badge-success',
            'absent' => 'badge badge-danger',
            'late' => 'badge badge-warning',
            'half_day' => 'badge badge-info',
            'leave' => 'badge badge-secondary',
            'holiday' => 'badge badge-primary'
        ];

        return $statusClasses[$this->status] ?? 'badge badge-secondary';
    }
}
