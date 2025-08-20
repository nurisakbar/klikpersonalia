<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
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
        // Get departments and positions from database for current company
        $departments = \App\Models\Department::byCompany(auth()->user()->company_id)
            ->active()
            ->pluck('name')
            ->toArray();
        
        $positions = \App\Models\Position::byCompany(auth()->user()->company_id)
            ->active()
            ->pluck('name')
            ->toArray();
        
        // Fallback to default values if no data in database
        if (empty($departments)) {
            $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'];
        }
        
        if (empty($positions)) {
            $positions = ['Staff', 'Senior Staff', 'Supervisor', 'Manager', 'Senior Manager', 'Director'];
        }
        
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'department' => 'required|string|in:' . implode(',', $departments),
            'position' => 'required|string|in:' . implode(',', $positions),
            'join_date' => 'required|date',
            'basic_salary' => 'required|numeric|min:1000000|max:999999999999', // Min 1 juta, max 999 miliar
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
        ];

        // Check if this is an update or create request
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // For update requests
            $employeeId = $this->route('employee');
            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($employeeId)
            ];
            $rules['status'] = 'required|string|in:active,inactive,terminated';
        } else {
            // For create requests
            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->where('company_id', auth()->user()->company_id)
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh karyawan lain.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'department.required' => 'Departemen wajib dipilih.',
            'department.in' => 'Departemen yang dipilih tidak valid.',
            'position.required' => 'Jabatan wajib dipilih.',
            'position.in' => 'Jabatan yang dipilih tidak valid.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
            'join_date.date' => 'Format tanggal bergabung tidak valid.',
            'basic_salary.required' => 'Gaji pokok wajib diisi.',
            'basic_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'basic_salary.min' => 'Gaji pokok minimal Rp 1.000.000.',
            'basic_salary.max' => 'Gaji pokok maksimal Rp 999.999.999.999.',
            'status.required' => 'Status karyawan wajib dipilih.',
            'status.in' => 'Status karyawan yang dipilih tidak valid.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'emergency_contact.max' => 'Kontak darurat maksimal 255 karakter.',
            'bank_name.max' => 'Nama bank maksimal 100 karakter.',
            'bank_account.max' => 'Nomor rekening maksimal 50 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'department' => 'departemen',
            'position' => 'jabatan',
            'join_date' => 'tanggal bergabung',
            'basic_salary' => 'gaji pokok',
            'status' => 'status',
            'address' => 'alamat',
            'emergency_contact' => 'kontak darurat',
            'bank_name' => 'nama bank',
            'bank_account' => 'nomor rekening',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Remove formatting from salary if it exists
        if ($this->has('basic_salary')) {
            $salary = $this->input('basic_salary');
            // Remove non-numeric characters except decimal point
            $cleanSalary = preg_replace('/[^\d.]/', '', $salary);
            $this->merge(['basic_salary' => $cleanSalary]);
        }
    }
}
