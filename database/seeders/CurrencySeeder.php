<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'decimal_places' => 2, 'is_base_currency' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'symbol' => 'ر.ق', 'decimal_places' => 2, 'is_base_currency' => false],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => 'ر.ع.', 'decimal_places' => 3, 'is_base_currency' => false],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'decimal_places' => 3, 'is_base_currency' => false],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => '.د.ب', 'decimal_places' => 3, 'is_base_currency' => false],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
