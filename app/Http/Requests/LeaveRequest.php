<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class LeaveRequest extends FormRequest
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
        $rules = [
            'leave_type' => 'required|in:annual,sick,maternity,paternity,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];

        // Jika ini adalah update request
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'leave_type.required' => 'Jenis cuti harus dipilih.',
            'leave_type.in' => 'Jenis cuti tidak valid.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'reason.required' => 'Alasan cuti harus diisi.',
            'reason.max' => 'Alasan cuti maksimal 500 karakter.',
            'attachment.file' => 'File lampiran harus berupa file.',
            'attachment.mimes' => 'File lampiran harus berformat PDF, JPG, JPEG, atau PNG.',
            'attachment.max' => 'Ukuran file lampiran maksimal 2MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validasi tambahan untuk tanggal
            if ($this->start_date && $this->end_date) {
                $startDate = Carbon::parse($this->start_date);
                $endDate = Carbon::parse($this->end_date);
                
                // Validasi maksimal durasi cuti (misal: 30 hari)
                $diffInDays = $startDate->diffInDays($endDate) + 1;
                if ($diffInDays > 30) {
                    $validator->errors()->add('end_date', 'Durasi cuti maksimal 30 hari.');
                }
            }
        });
    }
}
