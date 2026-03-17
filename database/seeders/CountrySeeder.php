<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'United Arab Emirates', 'iso_code' => 'AE', 'iso3_code' => 'ARE', 'phone_code' => '+971', 'currency_code' => 'AED', 'tax_type' => 'VAT', 'tax_percentage' => 5.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'UAE_VAT'],
            ['name' => 'United Kingdom', 'iso_code' => 'GB', 'iso3_code' => 'GBR', 'phone_code' => '+44', 'currency_code' => 'GBP', 'tax_type' => 'VAT', 'tax_percentage' => 20.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Apr-Mar', 'accounting_rules_profile' => 'UK_VAT'],
            ['name' => 'India', 'iso_code' => 'IN', 'iso3_code' => 'IND', 'phone_code' => '+91', 'currency_code' => 'INR', 'tax_type' => 'GST', 'tax_percentage' => 18.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Apr-Mar', 'accounting_rules_profile' => 'INDIA_GST'],
            ['name' => 'United States', 'iso_code' => 'US', 'iso3_code' => 'USA', 'phone_code' => '+1', 'currency_code' => 'USD', 'tax_type' => 'SALES_TAX', 'tax_percentage' => 0.00, 'date_format' => 'm/d/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'US_STANDARD'],
            ['name' => 'Saudi Arabia', 'iso_code' => 'SA', 'iso3_code' => 'SAU', 'phone_code' => '+966', 'currency_code' => 'SAR', 'tax_type' => 'VAT', 'tax_percentage' => 15.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'GCC_VAT'],
            ['name' => 'Qatar', 'iso_code' => 'QA', 'iso3_code' => 'QAT', 'phone_code' => '+974', 'currency_code' => 'QAR', 'tax_type' => 'NONE', 'tax_percentage' => 0.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'GCC_STANDARD'],
            ['name' => 'Oman', 'iso_code' => 'OM', 'iso3_code' => 'OMN', 'phone_code' => '+968', 'currency_code' => 'OMR', 'tax_type' => 'VAT', 'tax_percentage' => 5.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'GCC_VAT'],
            ['name' => 'Bahrain', 'iso_code' => 'BH', 'iso3_code' => 'BHR', 'phone_code' => '+973', 'currency_code' => 'BHD', 'tax_type' => 'VAT', 'tax_percentage' => 10.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'GCC_VAT'],
            ['name' => 'Kuwait', 'iso_code' => 'KW', 'iso3_code' => 'KWT', 'phone_code' => '+965', 'currency_code' => 'KWD', 'tax_type' => 'NONE', 'tax_percentage' => 0.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'GCC_STANDARD'],
            ['name' => 'Germany', 'iso_code' => 'DE', 'iso3_code' => 'DEU', 'phone_code' => '+49', 'currency_code' => 'EUR', 'tax_type' => 'VAT', 'tax_percentage' => 19.00, 'date_format' => 'd.m.Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'EU_VAT'],
            ['name' => 'France', 'iso_code' => 'FR', 'iso3_code' => 'FRA', 'phone_code' => '+33', 'currency_code' => 'EUR', 'tax_type' => 'VAT', 'tax_percentage' => 20.00, 'date_format' => 'd/m/Y', 'financial_year_format' => 'Jan-Dec', 'accounting_rules_profile' => 'EU_VAT'],
        ];

        foreach ($countries as $data) {
            $currency = Currency::where('code', $data['currency_code'])->first();
            unset($data['currency_code']);
            $data['default_currency_id'] = $currency?->id;
            Country::updateOrCreate(['iso_code' => $data['iso_code']], $data);
        }
    }
}
