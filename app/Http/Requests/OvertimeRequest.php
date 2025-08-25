<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class OvertimeRequest extends FormRequest
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
            'overtime_type' => 'required|in:regular,holiday,weekend,emergency',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];

        // Jika ini adalah update request
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['date'] = 'required|date';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'overtime_type.required' => 'Jenis lembur harus dipilih.',
            'overtime_type.in' => 'Jenis lembur tidak valid.',
            'date.required' => 'Tanggal lembur harus diisi.',
            'date.after_or_equal' => 'Tanggal lembur tidak boleh sebelum hari ini.',
            'start_time.required' => 'Waktu mulai harus diisi.',
            'start_time.date_format' => 'Format waktu mulai tidak valid.',
            'end_time.required' => 'Waktu selesai harus diisi.',
            'end_time.date_format' => 'Format waktu selesai tidak valid.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
            'reason.required' => 'Alasan lembur harus diisi.',
            'reason.max' => 'Alasan lembur maksimal 500 karakter.',
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
            // Validasi tambahan untuk waktu
            if ($this->start_time && $this->end_time) {
                try {
                    $startTime = Carbon::parse($this->start_time);
                    $endTime = Carbon::parse($this->end_time);
                    
                    // Jika end time sebelum start time, berarti lembur sampai hari berikutnya
                    if ($endTime < $startTime) {
                        $endTime->addDay();
                    }
                    
                    // Hitung durasi dalam menit
                    $diffInMinutes = $startTime->diffInMinutes($endTime);
                    $diffInHours = ceil($diffInMinutes / 60);
                    
                    // Validasi maksimal durasi lembur (8 jam)
                    if ($diffInHours > 8) {
                        $validator->errors()->add('end_time', 'Durasi lembur maksimal 8 jam per hari.');
                    }
                    
                    // Validasi minimal durasi lembur (1 jam)
                    if ($diffInHours < 1) {
                        $validator->errors()->add('end_time', 'Durasi lembur minimal 1 jam.');
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add('start_time', 'Format waktu tidak valid. Gunakan format HH:MM (contoh: 18:30).');
                }
            }
        });
    }
}
