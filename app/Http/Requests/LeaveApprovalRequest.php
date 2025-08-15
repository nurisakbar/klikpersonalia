<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveApprovalRequest extends FormRequest
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
        $rules = [
            'approval_notes' => 'nullable|string|max:1000',
        ];

        // Jika ini adalah rejection, approval_notes menjadi required
        if ($this->route()->getName() === 'leaves.reject') {
            $rules['approval_notes'] = 'required|string|max:1000';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'approval_notes.required' => 'Catatan approval/rejection harus diisi.',
            'approval_notes.max' => 'Catatan approval/rejection maksimal 1000 karakter.',
        ];
    }
}
