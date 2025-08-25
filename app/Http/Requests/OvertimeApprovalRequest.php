<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OvertimeApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'hr', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'approval_notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'approval_notes.max' => 'Catatan persetujuan maksimal 500 karakter.',
        ];
    }
}
