<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Company;

class DepartmentPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get RAJARTAN company
        $rajartanCompany = Company::where('name', 'RAJARTAN')->first();
        
        if (!$rajartanCompany) {
            $this->command->error('Company RAJARTAN not found!');
            return;
        }

        $this->command->info('Adding departments for RAJARTAN company...');

        // Create departments for RAJARTAN
        $departments = [
            [
                'name' => 'Information Technology',
                'description' => 'Departemen yang menangani teknologi informasi dan sistem',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Departemen yang menangani sumber daya manusia',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Finance',
                'description' => 'Departemen yang menangani keuangan dan akuntansi',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Marketing',
                'description' => 'Departemen yang menangani pemasaran dan penjualan',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Operations',
                'description' => 'Departemen yang menangani operasional perusahaan',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Departemen yang menangani layanan pelanggan',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ]
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        $this->command->info('Adding positions for RAJARTAN company...');

        // Create positions for RAJARTAN
        $positions = [
            [
                'name' => 'Staff',
                'description' => 'Posisi staff level',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Senior Staff',
                'description' => 'Posisi senior staff',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Supervisor',
                'description' => 'Posisi supervisor',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Manager',
                'description' => 'Posisi manager',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Senior Manager',
                'description' => 'Posisi senior manager',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Director',
                'description' => 'Posisi director',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'Software Developer',
                'description' => 'Pengembang perangkat lunak',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ],
            [
                'name' => 'System Administrator',
                'description' => 'Administrator sistem',
                'status' => true,
                'company_id' => $rajartanCompany->id
            ]
        ];

        foreach ($positions as $pos) {
            Position::create($pos);
        }

        $this->command->info('Successfully added departments and positions for RAJARTAN company!');
    }
}
