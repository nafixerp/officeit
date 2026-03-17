<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        User::updateOrCreate(
            ['email' => 'admin@officeit.com'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@officeit.com',
                'password' => Hash::make('admin123'),
                'company_id' => $company?->id,
                'role' => 'SUPER_ADMIN',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'accountant@officeit.com'],
            [
                'name' => 'Chief Accountant',
                'email' => 'accountant@officeit.com',
                'password' => Hash::make('password123'),
                'company_id' => $company?->id,
                'role' => 'ACCOUNTS_MANAGER',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'sales@officeit.com'],
            [
                'name' => 'Sales Manager',
                'email' => 'sales@officeit.com',
                'password' => Hash::make('password123'),
                'company_id' => $company?->id,
                'role' => 'SALES_MANAGER',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'hr@officeit.com'],
            [
                'name' => 'HR Manager',
                'email' => 'hr@officeit.com',
                'password' => Hash::make('password123'),
                'company_id' => $company?->id,
                'role' => 'HR_MANAGER',
                'is_active' => true,
            ]
        );
    }
}
