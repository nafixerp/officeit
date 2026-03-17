<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        $accounts = [
            // ASSETS
            ['code' => '1000', 'name' => 'Assets', 'type' => 'ASSET', 'is_group' => true, 'is_system' => true, 'children' => [
                ['code' => '1100', 'name' => 'Current Assets', 'type' => 'ASSET', 'is_group' => true, 'children' => [
                    ['code' => '1101', 'name' => 'Cash in Hand', 'type' => 'ASSET', 'sub_type' => 'CASH'],
                    ['code' => '1102', 'name' => 'Petty Cash', 'type' => 'ASSET', 'sub_type' => 'CASH'],
                    ['code' => '1110', 'name' => 'Bank Accounts', 'type' => 'ASSET', 'is_group' => true, 'sub_type' => 'BANK', 'children' => [
                        ['code' => '1111', 'name' => 'Main Bank Account', 'type' => 'ASSET', 'sub_type' => 'BANK'],
                        ['code' => '1112', 'name' => 'Savings Account', 'type' => 'ASSET', 'sub_type' => 'BANK'],
                    ]],
                    ['code' => '1200', 'name' => 'Accounts Receivable', 'type' => 'ASSET', 'sub_type' => 'RECEIVABLE', 'is_system' => true],
                    ['code' => '1210', 'name' => 'Inventory', 'type' => 'ASSET', 'sub_type' => 'INVENTORY'],
                    ['code' => '1220', 'name' => 'Prepaid Expenses', 'type' => 'ASSET'],
                    ['code' => '1230', 'name' => 'Advances to Suppliers', 'type' => 'ASSET'],
                    ['code' => '1240', 'name' => 'Staff Advances', 'type' => 'ASSET'],
                    ['code' => '1250', 'name' => 'Deposits', 'type' => 'ASSET'],
                    ['code' => '1260', 'name' => 'Input Tax (VAT/GST)', 'type' => 'ASSET', 'sub_type' => 'TAX', 'is_system' => true],
                ]],
                ['code' => '1500', 'name' => 'Fixed Assets', 'type' => 'ASSET', 'is_group' => true, 'children' => [
                    ['code' => '1510', 'name' => 'Furniture & Fixtures', 'type' => 'ASSET'],
                    ['code' => '1520', 'name' => 'Office Equipment', 'type' => 'ASSET'],
                    ['code' => '1530', 'name' => 'Computers & IT Equipment', 'type' => 'ASSET'],
                    ['code' => '1540', 'name' => 'Vehicles', 'type' => 'ASSET'],
                    ['code' => '1550', 'name' => 'Machinery', 'type' => 'ASSET'],
                    ['code' => '1590', 'name' => 'Accumulated Depreciation', 'type' => 'ASSET'],
                ]],
            ]],

            // LIABILITIES
            ['code' => '2000', 'name' => 'Liabilities', 'type' => 'LIABILITY', 'is_group' => true, 'is_system' => true, 'children' => [
                ['code' => '2100', 'name' => 'Current Liabilities', 'type' => 'LIABILITY', 'is_group' => true, 'children' => [
                    ['code' => '2101', 'name' => 'Accounts Payable', 'type' => 'LIABILITY', 'sub_type' => 'PAYABLE', 'is_system' => true],
                    ['code' => '2110', 'name' => 'Output Tax (VAT/GST)', 'type' => 'LIABILITY', 'sub_type' => 'TAX', 'is_system' => true],
                    ['code' => '2120', 'name' => 'Salary Payable', 'type' => 'LIABILITY'],
                    ['code' => '2130', 'name' => 'Accrued Expenses', 'type' => 'LIABILITY'],
                    ['code' => '2140', 'name' => 'Customer Advances', 'type' => 'LIABILITY'],
                    ['code' => '2150', 'name' => 'End of Service Benefits Payable', 'type' => 'LIABILITY'],
                ]],
                ['code' => '2500', 'name' => 'Long Term Liabilities', 'type' => 'LIABILITY', 'is_group' => true, 'children' => [
                    ['code' => '2510', 'name' => 'Loans Payable', 'type' => 'LIABILITY'],
                ]],
            ]],

            // EQUITY
            ['code' => '3000', 'name' => 'Equity', 'type' => 'EQUITY', 'is_group' => true, 'is_system' => true, 'children' => [
                ['code' => '3100', 'name' => 'Capital Account', 'type' => 'EQUITY'],
                ['code' => '3200', 'name' => 'Retained Earnings', 'type' => 'EQUITY', 'is_system' => true],
                ['code' => '3300', 'name' => 'Drawings', 'type' => 'EQUITY'],
            ]],

            // INCOME
            ['code' => '4000', 'name' => 'Income', 'type' => 'INCOME', 'is_group' => true, 'is_system' => true, 'children' => [
                ['code' => '4100', 'name' => 'Sales Income', 'type' => 'INCOME', 'sub_type' => 'SALES', 'is_system' => true],
                ['code' => '4200', 'name' => 'Service Income', 'type' => 'INCOME', 'sub_type' => 'SERVICE'],
                ['code' => '4300', 'name' => 'Other Income', 'type' => 'INCOME'],
                ['code' => '4400', 'name' => 'Discount Received', 'type' => 'INCOME'],
                ['code' => '4500', 'name' => 'Exchange Gain', 'type' => 'INCOME'],
            ]],

            // EXPENSES
            ['code' => '5000', 'name' => 'Expenses', 'type' => 'EXPENSE', 'is_group' => true, 'is_system' => true, 'children' => [
                ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => 'EXPENSE', 'is_group' => true, 'children' => [
                    ['code' => '5101', 'name' => 'Purchase Account', 'type' => 'EXPENSE', 'sub_type' => 'PURCHASE', 'is_system' => true],
                    ['code' => '5102', 'name' => 'Direct Expenses', 'type' => 'EXPENSE'],
                ]],
                ['code' => '5200', 'name' => 'Operating Expenses', 'type' => 'EXPENSE', 'is_group' => true, 'children' => [
                    ['code' => '5201', 'name' => 'Salary Expense', 'type' => 'EXPENSE', 'sub_type' => 'SALARY'],
                    ['code' => '5202', 'name' => 'Rent Expense', 'type' => 'EXPENSE'],
                    ['code' => '5203', 'name' => 'Utility Expense', 'type' => 'EXPENSE'],
                    ['code' => '5204', 'name' => 'Office Expense', 'type' => 'EXPENSE'],
                    ['code' => '5205', 'name' => 'Travel Expense', 'type' => 'EXPENSE'],
                    ['code' => '5206', 'name' => 'Marketing Expense', 'type' => 'EXPENSE'],
                    ['code' => '5207', 'name' => 'Repair & Maintenance', 'type' => 'EXPENSE'],
                    ['code' => '5208', 'name' => 'Communication Expense', 'type' => 'EXPENSE'],
                    ['code' => '5209', 'name' => 'Transportation Expense', 'type' => 'EXPENSE'],
                    ['code' => '5210', 'name' => 'Legal & Professional Fees', 'type' => 'EXPENSE'],
                    ['code' => '5211', 'name' => 'Insurance Expense', 'type' => 'EXPENSE'],
                    ['code' => '5212', 'name' => 'Bank Charges', 'type' => 'EXPENSE'],
                    ['code' => '5213', 'name' => 'Depreciation Expense', 'type' => 'EXPENSE'],
                    ['code' => '5214', 'name' => 'Exchange Loss', 'type' => 'EXPENSE'],
                    ['code' => '5215', 'name' => 'Discount Given', 'type' => 'EXPENSE'],
                    ['code' => '5216', 'name' => 'Visa Expenses', 'type' => 'EXPENSE'],
                    ['code' => '5217', 'name' => 'Courier & Postage', 'type' => 'EXPENSE'],
                    ['code' => '5218', 'name' => 'Printing & Stationery', 'type' => 'EXPENSE'],
                    ['code' => '5219', 'name' => 'Miscellaneous Expense', 'type' => 'EXPENSE'],
                ]],
            ]],
        ];

        $this->createAccounts($accounts, $company->id, null);
    }

    private function createAccounts(array $accounts, int $companyId, ?int $parentId): void
    {
        foreach ($accounts as $data) {
            $children = $data['children'] ?? [];
            unset($data['children']);

            $account = ChartOfAccount::updateOrCreate(
                ['company_id' => $companyId, 'code' => $data['code']],
                array_merge($data, [
                    'company_id' => $companyId,
                    'parent_id' => $parentId,
                    'is_group' => $data['is_group'] ?? false,
                    'is_system' => $data['is_system'] ?? false,
                ])
            );

            if (!empty($children)) {
                $this->createAccounts($children, $companyId, $account->id);
            }
        }
    }
}
