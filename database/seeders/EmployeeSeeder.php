<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Company;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company (assuming CompanySeeder runs first)
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found. Please run CompanySeeder first.');
            return;
        }

        $employees = [
            [
                'employee_id' => 'EMP001',
                'name' => 'John Doe',
                'email' => 'john.doe@klikmedis.com',
                'phone' => '081234567890',
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'join_date' => '2024-01-15',
                'department' => 'IT',
                'position' => 'Senior Developer',
                'ptkp_status' => 'TK/0',
                'basic_salary' => 8000000,
                'status' => 'active',
                'emergency_contact' => '081234567891',
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'bpjs_kesehatan_number' => '0001234567890',
                'bpjs_ketenagakerjaan_number' => '0001234567890',
                'bpjs_kesehatan_active' => true,
                'bpjs_ketenagakerjaan_active' => true,
                'bpjs_effective_date' => '2024-01-15',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP002',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@klikmedis.com',
                'phone' => '081234567892',
                'address' => 'Jl. Thamrin No. 45, Jakarta Pusat',
                'join_date' => '2024-02-01',
                'department' => 'HR',
                'position' => 'HR Manager',
                'ptkp_status' => 'K/1',
                'basic_salary' => 7000000,
                'status' => 'active',
                'emergency_contact' => '081234567893',
                'bank_name' => 'Mandiri',
                'bank_account' => '0987654321',
                'bpjs_kesehatan_number' => '0001234567891',
                'bpjs_ketenagakerjaan_number' => '0001234567891',
                'bpjs_kesehatan_active' => true,
                'bpjs_ketenagakerjaan_active' => true,
                'bpjs_effective_date' => '2024-02-01',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP003',
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@klikmedis.com',
                'phone' => '081234567894',
                'address' => 'Jl. Gatot Subroto No. 67, Jakarta Selatan',
                'join_date' => '2024-02-10',
                'department' => 'Finance',
                'position' => 'Accountant',
                'ptkp_status' => 'TK/1',
                'basic_salary' => 6500000,
                'status' => 'active',
                'emergency_contact' => '081234567895',
                'bank_name' => 'BNI',
                'bank_account' => '1122334455',
                'bpjs_kesehatan_number' => '0001234567892',
                'bpjs_ketenagakerjaan_number' => '0001234567892',
                'bpjs_kesehatan_active' => true,
                'bpjs_ketenagakerjaan_active' => true,
                'bpjs_effective_date' => '2024-02-10',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP004',
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@klikmedis.com',
                'phone' => '081234567896',
                'address' => 'Jl. Kuningan No. 89, Jakarta Selatan',
                'join_date' => '2024-02-15',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'ptkp_status' => 'K/2',
                'basic_salary' => 6000000,
                'status' => 'active',
                'emergency_contact' => '081234567897',
                'bank_name' => 'BRI',
                'bank_account' => '5566778899',
                'bpjs_kesehatan_number' => '0001234567893',
                'bpjs_ketenagakerjaan_number' => '0001234567893',
                'bpjs_kesehatan_active' => true,
                'bpjs_ketenagakerjaan_active' => true,
                'bpjs_effective_date' => '2024-02-15',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP005',
                'name' => 'David Brown',
                'email' => 'david.brown@klikmedis.com',
                'phone' => '081234567898',
                'address' => 'Jl. Senayan No. 12, Jakarta Pusat',
                'join_date' => '2024-01-20',
                'department' => 'Sales',
                'position' => 'Sales Manager',
                'ptkp_status' => 'K/3',
                'basic_salary' => 7500000,
                'status' => 'active',
                'emergency_contact' => '081234567899',
                'bank_name' => 'BCA',
                'bank_account' => '9988776655',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP006',
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@klikmedis.com',
                'phone' => '081234567800',
                'address' => 'Jl. Kebayoran Baru No. 34, Jakarta Selatan',
                'join_date' => '2024-03-01',
                'department' => 'IT',
                'position' => 'Frontend Developer',
                'basic_salary' => 5500000,
                'status' => 'active',
                'emergency_contact' => '081234567801',
                'bank_name' => 'Mandiri',
                'bank_account' => '4433221100',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP007',
                'name' => 'Robert Taylor',
                'email' => 'robert.taylor@klikmedis.com',
                'phone' => '081234567802',
                'address' => 'Jl. Menteng No. 56, Jakarta Pusat',
                'join_date' => '2024-03-10',
                'department' => 'Finance',
                'position' => 'Financial Analyst',
                'basic_salary' => 5800000,
                'status' => 'active',
                'emergency_contact' => '081234567803',
                'bank_name' => 'BNI',
                'bank_account' => '6677889900',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP008',
                'name' => 'Emily Davis',
                'email' => 'emily.davis@klikmedis.com',
                'phone' => '081234567804',
                'address' => 'Jl. Pondok Indah No. 78, Jakarta Selatan',
                'join_date' => '2024-03-15',
                'department' => 'Marketing',
                'position' => 'Digital Marketing',
                'basic_salary' => 5200000,
                'status' => 'active',
                'emergency_contact' => '081234567805',
                'bank_name' => 'BRI',
                'bank_account' => '7788990011',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP009',
                'name' => 'Michael Wilson',
                'email' => 'michael.wilson@klikmedis.com',
                'phone' => '081234567806',
                'address' => 'Jl. Kelapa Gading No. 90, Jakarta Utara',
                'join_date' => '2024-01-10',
                'department' => 'Sales',
                'position' => 'Sales Representative',
                'basic_salary' => 4800000,
                'status' => 'inactive',
                'emergency_contact' => '081234567807',
                'bank_name' => 'BCA',
                'bank_account' => '8899001122',
                'company_id' => $company->id,
            ],
            [
                'employee_id' => 'EMP010',
                'name' => 'Jennifer Lee',
                'email' => 'jennifer.lee@klikmedis.com',
                'phone' => '081234567808',
                'address' => 'Jl. Pluit No. 23, Jakarta Utara',
                'join_date' => '2024-02-20',
                'department' => 'HR',
                'position' => 'HR Specialist',
                'basic_salary' => 5200000,
                'status' => 'active',
                'emergency_contact' => '081234567809',
                'bank_name' => 'Mandiri',
                'bank_account' => '9900112233',
                'company_id' => $company->id,
            ]
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }

        $this->command->info('Employee data seeded successfully!');
    }
}
