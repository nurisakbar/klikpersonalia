<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::create([
            'name' => 'KlikMedis',
            'email' => 'info@klikmedis.com',
            'phone' => '021-12345678',
            'address' => 'Jl. Sudirman No. 123',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
            'country' => 'Indonesia',
            'website' => 'https://klikmedis.com',
            'tax_number' => '123456789012345',
            'business_number' => 'SIUP-123456789',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'subscription_start' => now(),
            'subscription_end' => now()->addYear(),
            'max_employees' => 100,
            'is_trial' => false,
        ]);

        $this->command->info('Company created successfully!');
        $this->command->info('Company ID: ' . $company->id);
    }
} 
