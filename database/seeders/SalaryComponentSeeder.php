<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SalaryComponent;
use App\Models\Company;

class SalaryComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $this->createSalaryComponents($company);
        }
    }
    
    /**
     * Create salary components for a specific company
     */
    private function createSalaryComponents(Company $company): void
    {
        $components = [
            // Earning Components (Pendapatan)
            [
                'name' => 'Gaji Pokok',
                'description' => 'Gaji pokok bulanan karyawan',
                'default_value' => 5000000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => true,
                'is_bpjs_calculated' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Tunjangan Makan',
                'description' => 'Tunjangan makan harian karyawan',
                'default_value' => 25000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 2
            ],
            [
                'name' => 'Tunjangan Transport',
                'description' => 'Tunjangan transportasi bulanan',
                'default_value' => 500000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 3
            ],
            [
                'name' => 'Tunjangan Jabatan',
                'description' => 'Tunjangan berdasarkan jabatan/posisi',
                'default_value' => 1000000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => true,
                'is_bpjs_calculated' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Tunjangan Kinerja',
                'description' => 'Tunjangan berdasarkan kinerja karyawan',
                'default_value' => 750000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => true,
                'is_bpjs_calculated' => false,
                'sort_order' => 5
            ],
            [
                'name' => 'Tunjangan Lembur',
                'description' => 'Tunjangan untuk jam kerja lembur',
                'default_value' => 0,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => true,
                'is_bpjs_calculated' => false,
                'sort_order' => 6
            ],
            [
                'name' => 'Bonus Tahunan',
                'description' => 'Bonus tahunan (THR)',
                'default_value' => 0,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => true,
                'is_bpjs_calculated' => false,
                'sort_order' => 7
            ],
            [
                'name' => 'Tunjangan Kesehatan',
                'description' => 'Tunjangan kesehatan tambahan',
                'default_value' => 300000,
                'type' => 'earning',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 8
            ],
            
            // Deduction Components (Potongan)
            [
                'name' => 'Potongan BPJS Kesehatan',
                'description' => 'Potongan BPJS Kesehatan (2% dari gaji pokok)',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => true,
                'sort_order' => 9
            ],
            [
                'name' => 'Potongan BPJS Ketenagakerjaan',
                'description' => 'Potongan BPJS Ketenagakerjaan (2% dari gaji pokok)',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => true,
                'sort_order' => 10
            ],
            [
                'name' => 'Potongan Pajak',
                'description' => 'Potongan pajak penghasilan (PPh 21)',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 11
            ],
            [
                'name' => 'Potongan Keterlambatan',
                'description' => 'Potongan untuk keterlambatan datang',
                'default_value' => 50000,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 12
            ],
            [
                'name' => 'Potongan Cuti',
                'description' => 'Potongan untuk cuti yang melebihi hak',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 13
            ],
            [
                'name' => 'Potongan Pinjaman',
                'description' => 'Potongan untuk pinjaman karyawan',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 14
            ],
            [
                'name' => 'Potongan Lainnya',
                'description' => 'Potongan lainnya yang tidak termasuk kategori di atas',
                'default_value' => 0,
                'type' => 'deduction',
                'is_active' => true,
                'is_taxable' => false,
                'is_bpjs_calculated' => false,
                'sort_order' => 15
            ]
        ];
        
        foreach ($components as $componentData) {
            SalaryComponent::create(array_merge($componentData, [
                'company_id' => $company->id
            ]));
        }
        
        $this->command->info("Salary components created for company: {$company->name}");
    }
}
