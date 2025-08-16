<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'hr']);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string|max:20',
            'basic_salary' => 'required|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'bpjs_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }
}


