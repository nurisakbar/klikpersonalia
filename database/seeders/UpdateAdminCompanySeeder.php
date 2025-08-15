<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateAdminCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update admin user to have company_id
        User::where('name', 'Administrator')->update([
            'company_id' => 'de5c6d3d-97e7-46a8-9eae-d20c792e5b98'
        ]);
        
        echo "Admin company_id updated successfully!\n";
    }
}
