<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'hr']);
    }

    public function failedAuthorization()
    {
        abort(403, 'Anda tidak memiliki izin untuk melakukan aksi ini.');
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Pilih karyawan terlebih dahulu.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak ditemukan.',
            'period.required' => 'Periode harus diisi.',
            'period.regex' => 'Format periode harus YYYY-MM (contoh: 2024-01).',
            'basic_salary.required' => 'Gaji pokok harus diisi.',
            'basic_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'basic_salary.min' => 'Gaji pokok minimal 0.',
            'allowance.numeric' => 'Tunjangan harus berupa angka.',
            'allowance.min' => 'Tunjangan minimal 0.',
            'deduction.numeric' => 'Potongan harus berupa angka.',
            'deduction.min' => 'Potongan minimal 0.',
        ];
    }
}


