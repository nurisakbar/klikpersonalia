<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@klikmedis.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create demo user
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@klikmedis.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@klikmedis.com / password');
        $this->command->info('Demo: demo@klikmedis.com / password');
    }
}
