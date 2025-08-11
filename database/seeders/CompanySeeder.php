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
            'name' => 'KlikMedis Indonesia',
            'email' => 'info@klikmedis.com',
            'phone' => '+62 21 1234 5678',
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
            'trial_ends_at' => now()->addDays(30),
        ]);

        $this->command->info('Company seeded successfully!');
        $this->command->info('Company ID: ' . $company->id);
    }
}
