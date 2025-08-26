<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryComponentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'is_active' => 'boolean',
            'is_taxable' => 'boolean',
            'is_bpjs_calculated' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ];

        // Add unique validation for name when updating
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('salary_components')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($this->route('salary_component'))
            ];
        } else {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('salary_components')
                    ->where('company_id', auth()->user()->company_id)
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama komponen wajib diisi.',
            'name.unique' => 'Nama komponen sudah ada dalam perusahaan ini.',
            'name.max' => 'Nama komponen maksimal 255 karakter.',
            'default_value.required' => 'Nilai default wajib diisi.',
            'default_value.numeric' => 'Nilai default harus berupa angka.',
            'default_value.min' => 'Nilai default minimal 0.',
            'type.required' => 'Tipe komponen wajib dipilih.',
            'type.in' => 'Tipe komponen tidak valid.',
            'sort_order.integer' => 'Urutan harus berupa angka.',
            'sort_order.min' => 'Urutan minimal 0.'
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama komponen',
            'description' => 'deskripsi',
            'default_value' => 'nilai default',
            'type' => 'tipe komponen',
            'is_active' => 'status aktif',
            'is_taxable' => 'dikenakan pajak',
            'is_bpjs_calculated' => 'dihitung BPJS',
            'sort_order' => 'urutan'
        ];
    }
}
