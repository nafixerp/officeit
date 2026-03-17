<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DefaultCompanySeeder extends Seeder
{
    public function run(): void
    {
        $uae = Country::where('iso_code', 'AE')->first();
        $aed = Currency::where('code', 'AED')->first();

        $company = Company::updateOrCreate(
            ['name' => 'Office IT International'],
            [
                'short_name' => 'OIT',
                'address' => 'Dubai, United Arab Emirates',
                'phone' => '+971-4-1234567',
                'email' => 'info@officeit.com',
                'website' => 'www.officeit.com',
                'country_id' => $uae?->id,
                'default_currency_id' => $aed?->id,
                'financial_year_start' => '2024-01-01',
                'timezone' => 'Asia/Dubai',
                'invoice_prefix' => 'OIT',
                'is_active' => true,
            ]
        );

        // Create branches
        $branches = [
            ['name' => 'Head Office - Dubai', 'code' => 'HO-DXB', 'country_id' => $uae?->id],
            ['name' => 'Abu Dhabi Branch', 'code' => 'BR-AUH', 'country_id' => $uae?->id],
        ];

        $ukCountry = Country::where('iso_code', 'GB')->first();
        if ($ukCountry) {
            $branches[] = ['name' => 'London Office', 'code' => 'BR-LDN', 'country_id' => $ukCountry->id];
        }

        $indiaCountry = Country::where('iso_code', 'IN')->first();
        if ($indiaCountry) {
            $branches[] = ['name' => 'Mumbai Office', 'code' => 'BR-MUM', 'country_id' => $indiaCountry->id];
        }

        foreach ($branches as $branchData) {
            Branch::updateOrCreate(
                ['company_id' => $company->id, 'code' => $branchData['code']],
                array_merge($branchData, ['company_id' => $company->id, 'is_active' => true])
            );
        }

        // Create departments
        $departments = ['Sales', 'Purchase', 'Accounts', 'HR', 'Admin', 'Operations', 'Projects'];
        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['company_id' => $company->id, 'name' => $dept],
                ['company_id' => $company->id, 'name' => $dept, 'is_active' => true]
            );
        }

        // Create designations
        $designations = [
            'Managing Director', 'General Manager', 'Branch Manager', 'Sales Manager',
            'Sales Executive', 'Purchase Manager', 'Purchase Officer', 'Accountant',
            'Senior Accountant', 'HR Manager', 'HR Officer', 'Admin Officer',
            'Office Manager', 'Store Keeper', 'Driver', 'Support Staff',
        ];
        foreach ($designations as $desig) {
            Designation::updateOrCreate(
                ['company_id' => $company->id, 'name' => $desig],
                ['company_id' => $company->id, 'name' => $desig, 'is_active' => true]
            );
        }
    }
}
