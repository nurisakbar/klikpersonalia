<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Company;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();

        foreach ($companies as $company) {
            $departments = [
                [
                    'name' => 'Information Technology',
                    'description' => 'Departemen yang menangani teknologi informasi dan sistem',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Human Resources',
                    'description' => 'Departemen yang menangani sumber daya manusia',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Finance',
                    'description' => 'Departemen yang menangani keuangan dan akuntansi',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Marketing',
                    'description' => 'Departemen yang menangani pemasaran dan penjualan',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Operations',
                    'description' => 'Departemen yang menangani operasional perusahaan',
                    'status' => true,
                    'company_id' => $company->id
                ],
                [
                    'name' => 'Customer Service',
                    'description' => 'Departemen yang menangani layanan pelanggan',
                    'status' => true,
                    'company_id' => $company->id
                ]
            ];

            foreach ($departments as $department) {
                Department::create($department);
            }
        }
    }
}
