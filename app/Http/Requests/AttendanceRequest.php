<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,half_day,leave,holiday',
            'notes' => 'nullable|string|max:500',
        ];

        // Check if this is an update or create request
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // For update requests - check for duplicate attendance excluding current record
            $attendanceId = $this->route('attendance');
            $rules['date'] = [
                'required',
                'date',
                Rule::unique('attendances', 'date')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id);
                })->ignore($attendanceId)
            ];
        } else {
            // For create requests - check for duplicate attendance
            $rules['date'] = [
                'required',
                'date',
                Rule::unique('attendances', 'date')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id);
                })
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.unique' => 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada.',
            'check_in.date_format' => 'Format waktu check-in tidak valid (HH:MM).',
            'check_out.date_format' => 'Format waktu check-out tidak valid (HH:MM).',
            'check_out.after' => 'Waktu check-out harus setelah waktu check-in.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'karyawan',
            'date' => 'tanggal',
            'check_in' => 'waktu check-in',
            'check_out' => 'waktu check-out',
            'status' => 'status',
            'notes' => 'catatan',
        ];
    }
}
