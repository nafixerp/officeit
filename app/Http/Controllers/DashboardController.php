<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;
        $today = Carbon::today();

        try {
            // Today's totals
            $todaySalesTotal = SalesInvoice::where('company_id', $companyId)
                ->whereDate('date', $today)
                ->where('status', '!=', 'CANCELLED')
                ->sum('total_amount') ?? 0;

            $todayPurchaseTotal = PurchaseInvoice::where('company_id', $companyId)
                ->whereDate('date', $today)
                ->where('status', '!=', 'CANCELLED')
                ->sum('total_amount') ?? 0;

            // Receipts and payments totals default to 0 (tables may not exist yet)
            $todayReceiptsTotal = 0;
            $todayPaymentsTotal = 0;

            // Bank balance
            $bankBalance = BankAccount::where('company_id', $companyId)
                ->where('is_active', true)
                ->sum('opening_balance') ?? 0;

            // Cash balance defaults to 0 (no cash account table yet)
            $cashBalance = 0;

            // Outstanding receivables (unpaid/partially paid sales invoices)
            $outstandingReceivables = SalesInvoice::where('company_id', $companyId)
                ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
                ->sum('balance_amount') ?? 0;

            // Outstanding payables (unpaid/partially paid purchase invoices)
            $outstandingPayables = PurchaseInvoice::where('company_id', $companyId)
                ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
                ->sum('balance_amount') ?? 0;

            // Recent transactions (last 10 sales invoices)
            $recentTransactions = SalesInvoice::where('company_id', $companyId)
                ->with('customer')
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // Top 5 customers by total amount
            $topCustomers = Customer::where('customers.company_id', $companyId)
                ->select('customers.id', 'customers.name', DB::raw('COALESCE(SUM(sales_invoices.total_amount), 0) as total_amount'))
                ->leftJoin('sales_invoices', function ($join) {
                    $join->on('customers.id', '=', 'sales_invoices.customer_id')
                        ->where('sales_invoices.status', '!=', 'CANCELLED');
                })
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get();

            // Top 5 suppliers by total amount
            $topSuppliers = Supplier::where('suppliers.company_id', $companyId)
                ->select('suppliers.id', 'suppliers.name', DB::raw('COALESCE(SUM(purchase_invoices.total_amount), 0) as total_amount'))
                ->leftJoin('purchase_invoices', function ($join) {
                    $join->on('suppliers.id', '=', 'purchase_invoices.supplier_id')
                        ->where('purchase_invoices.status', '!=', 'CANCELLED');
                })
                ->groupBy('suppliers.id', 'suppliers.name')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $todaySalesTotal = 0;
            $todayPurchaseTotal = 0;
            $todayReceiptsTotal = 0;
            $todayPaymentsTotal = 0;
            $bankBalance = 0;
            $cashBalance = 0;
            $outstandingReceivables = 0;
            $outstandingPayables = 0;
            $recentTransactions = collect();
            $topCustomers = collect();
            $topSuppliers = collect();
        }

        return view('dashboard.index', compact(
            'todaySalesTotal',
            'todayPurchaseTotal',
            'todayReceiptsTotal',
            'todayPaymentsTotal',
            'cashBalance',
            'bankBalance',
            'outstandingReceivables',
            'outstandingPayables',
            'recentTransactions',
            'topCustomers',
            'topSuppliers'
        ));
    }
}
