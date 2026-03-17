<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Country;
use App\Models\TaxProfile;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        $taxData = [
            'AE' => [
                'name' => 'UAE VAT',
                'tax_type' => 'VAT',
                'rates' => [
                    ['name' => 'Standard Rate', 'code' => 'SR', 'rate' => 5.00, 'type' => 'STANDARD', 'is_default' => true],
                    ['name' => 'Zero Rated', 'code' => 'ZR', 'rate' => 0.00, 'type' => 'ZERO_RATED'],
                    ['name' => 'Exempt', 'code' => 'EX', 'rate' => 0.00, 'type' => 'EXEMPT'],
                    ['name' => 'Reverse Charge', 'code' => 'RC', 'rate' => 5.00, 'type' => 'REVERSE_CHARGE'],
                ],
            ],
            'GB' => [
                'name' => 'UK VAT',
                'tax_type' => 'VAT',
                'rates' => [
                    ['name' => 'Standard Rate', 'code' => 'SR', 'rate' => 20.00, 'type' => 'STANDARD', 'is_default' => true],
                    ['name' => 'Reduced Rate', 'code' => 'RR', 'rate' => 5.00, 'type' => 'REDUCED'],
                    ['name' => 'Zero Rated', 'code' => 'ZR', 'rate' => 0.00, 'type' => 'ZERO_RATED'],
                    ['name' => 'Exempt', 'code' => 'EX', 'rate' => 0.00, 'type' => 'EXEMPT'],
                ],
            ],
            'IN' => [
                'name' => 'India GST',
                'tax_type' => 'GST',
                'rates' => [
                    ['name' => 'GST 5%', 'code' => 'GST5', 'rate' => 5.00, 'type' => 'STANDARD'],
                    ['name' => 'GST 12%', 'code' => 'GST12', 'rate' => 12.00, 'type' => 'STANDARD'],
                    ['name' => 'GST 18%', 'code' => 'GST18', 'rate' => 18.00, 'type' => 'STANDARD', 'is_default' => true],
                    ['name' => 'GST 28%', 'code' => 'GST28', 'rate' => 28.00, 'type' => 'STANDARD'],
                    ['name' => 'Exempt', 'code' => 'EX', 'rate' => 0.00, 'type' => 'EXEMPT'],
                ],
            ],
            'SA' => [
                'name' => 'Saudi VAT',
                'tax_type' => 'VAT',
                'rates' => [
                    ['name' => 'Standard Rate', 'code' => 'SR', 'rate' => 15.00, 'type' => 'STANDARD', 'is_default' => true],
                    ['name' => 'Zero Rated', 'code' => 'ZR', 'rate' => 0.00, 'type' => 'ZERO_RATED'],
                    ['name' => 'Exempt', 'code' => 'EX', 'rate' => 0.00, 'type' => 'EXEMPT'],
                ],
            ],
        ];

        foreach ($taxData as $isoCode => $data) {
            $country = Country::where('iso_code', $isoCode)->first();
            if (!$country) continue;

            $profile = TaxProfile::updateOrCreate(
                ['company_id' => $company->id, 'country_id' => $country->id],
                [
                    'company_id' => $company->id,
                    'country_id' => $country->id,
                    'name' => $data['name'],
                    'tax_type' => $data['tax_type'],
                    'is_active' => true,
                ]
            );

            foreach ($data['rates'] as $rateData) {
                TaxRate::updateOrCreate(
                    ['tax_profile_id' => $profile->id, 'code' => $rateData['code']],
                    array_merge($rateData, [
                        'tax_profile_id' => $profile->id,
                        'is_default' => $rateData['is_default'] ?? false,
                        'is_active' => true,
                    ])
                );
            }
        }
    }
}
