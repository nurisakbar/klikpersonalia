<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tax;

class TaxRequest extends FormRequest
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
        // Default rules for both create and update
        $rules = [
            'taxable_income' => 'required|numeric|min:1000000',
            'ptkp_status' => 'required|in:' . implode(',', array_keys(Tax::PTKP_STATUSES)),
            'notes' => 'nullable|string|max:1000',
        ];

        // On create, require employee and period
        if ($this->isMethod('POST')) {
            $rules['employee_id'] = 'required|exists:employees,id';
            $rules['tax_period'] = 'required|date_format:Y-m';
        }

        // On update, employee and period are immutable; accept if present but not required
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['employee_id'] = 'sometimes|exists:employees,id';
            $rules['tax_period'] = 'sometimes|date_format:Y-m';
            $rules['status'] = 'required|in:' . implode(',', [
                Tax::STATUS_PENDING,
                Tax::STATUS_CALCULATED,
                Tax::STATUS_PAID,
                Tax::STATUS_VERIFIED
            ]);
        }

        return $rules;
    }



    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'karyawan',
            'tax_period' => 'periode pajak',
            'taxable_income' => 'pendapatan kena pajak',
            'ptkp_status' => 'status PTKP',
            'status' => 'status pajak',
            'notes' => 'catatan',
        ];
    }
}
