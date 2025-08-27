<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BpjsRequest extends FormRequest
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
            'bpjs_period' => 'required|date_format:Y-m',
            'bpjs_type' => ['required', Rule::in(['kesehatan', 'ketenagakerjaan'])],
            'base_salary' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ];

        // Add status and payment_date validation for updates
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['status'] = ['required', Rule::in(['pending', 'calculated', 'paid', 'verified'])];
            $rules['payment_date'] = 'nullable|date';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan harus dipilih.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak ditemukan.',
            'bpjs_period.required' => 'Periode BPJS harus diisi.',
            'bpjs_period.date_format' => 'Format periode BPJS harus YYYY-MM.',
            'bpjs_type.required' => 'Jenis BPJS harus dipilih.',
            'bpjs_type.in' => 'Jenis BPJS harus Kesehatan atau Ketenagakerjaan.',
            'base_salary.required' => 'Gaji pokok harus diisi.',
            'base_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'base_salary.min' => 'Gaji pokok tidak boleh negatif.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status harus Pending, Calculated, Paid, atau Verified.',
            'payment_date.date' => 'Tanggal pembayaran harus berupa tanggal yang valid.',
            'notes.max' => 'Catatan tidak boleh lebih dari 1000 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'karyawan',
            'bpjs_period' => 'periode BPJS',
            'bpjs_type' => 'jenis BPJS',
            'base_salary' => 'gaji pokok',
            'status' => 'status',
            'payment_date' => 'tanggal pembayaran',
            'notes' => 'catatan',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure base_salary is numeric
        if ($this->has('base_salary')) {
            $this->merge([
                'base_salary' => (float) str_replace(['Rp', '.', ','], '', $this->base_salary)
            ]);
        }
    }
}
