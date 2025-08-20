<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Company;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            $positions = [
                [
                    'name' => 'Manager',
                    'description' => 'Jabatan manajerial yang memimpin tim atau departemen',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Senior Developer',
                    'description' => 'Pengembang senior dengan pengalaman tinggi',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Developer',
                    'description' => 'Pengembang aplikasi dan sistem',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'HR Specialist',
                    'description' => 'Spesialis sumber daya manusia',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Accountant',
                    'description' => 'Akuntan yang menangani laporan keuangan',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Marketing Specialist',
                    'description' => 'Spesialis pemasaran dan promosi',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Customer Service Representative',
                    'description' => 'Perwakilan layanan pelanggan',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Administrator',
                    'description' => 'Administrator sistem dan data',
                    'status' => true,
                    'company_id' => $company->id
                ]
            ];

            foreach ($positions as $position) {
                Position::create($position);
            }
        }
    }
}
