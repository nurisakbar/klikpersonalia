<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxReportRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'employee_id' => 'nullable|exists:employees,id',
            'status' => 'nullable|in:pending,calculated,paid,verified',
            'tax_period' => 'nullable|string',
            'export_format' => 'nullable|in:pdf,excel'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'end_date.date' => 'Tanggal akhir harus berupa tanggal yang valid',
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai',
            'employee_id.exists' => 'Karyawan yang dipilih tidak ditemukan',
            'status.in' => 'Status yang dipilih tidak valid',
            'export_format.in' => 'Format export yang dipilih tidak valid'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
            'employee_id' => 'karyawan',
            'status' => 'status',
            'tax_period' => 'periode pajak',
            'export_format' => 'format export'
        ];
    }
}
