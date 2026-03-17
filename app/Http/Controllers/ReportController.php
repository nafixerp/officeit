<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\JournalLine;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PurchaseInvoice;
use App\Models\Receipt;
use App\Models\SalesInvoice;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function salesReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $invoices = SalesInvoice::where('company_id', $companyId)
            ->when($request->from_date, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->where('date', '<=', $v))
            ->when($request->customer_id, fn ($q, $v) => $q->where('customer_id', $v))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with(['customer', 'branch'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $countries = Country::where('is_active', true)->get();

        $totals = [
            'subtotal' => $invoices->sum('subtotal'),
            'tax_amount' => $invoices->sum('tax_amount'),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_amount' => $invoices->sum('balance_amount'),
        ];

        return view('reports.sales', compact('invoices', 'customers', 'branches', 'countries', 'totals'));
    }

    public function purchaseReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $invoices = PurchaseInvoice::where('company_id', $companyId)
            ->when($request->from_date, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->where('date', '<=', $v))
            ->when($request->supplier_id, fn ($q, $v) => $q->where('supplier_id', $v))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with(['supplier', 'branch'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $countries = Country::where('is_active', true)->get();

        $totals = [
            'subtotal' => $invoices->sum('subtotal'),
            'tax_amount' => $invoices->sum('tax_amount'),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'balance_amount' => $invoices->sum('balance_amount'),
        ];

        return view('reports.purchase', compact('invoices', 'suppliers', 'branches', 'countries', 'totals'));
    }

    public function receiptReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $receipts = Receipt::where('company_id', $companyId)
            ->when($request->from_date, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->where('date', '<=', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with(['customer', 'bankAccount'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        $totals = [
            'total_amount' => $receipts->sum('amount'),
        ];

        return view('reports.receipts', compact('receipts', 'totals'));
    }

    public function paymentReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $payments = Payment::where('company_id', $companyId)
            ->when($request->from_date, fn ($q, $v) => $q->where('date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->where('date', '<=', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with(['supplier', 'bankAccount'])
            ->orderBy('date', 'desc')
            ->paginate(50);

        $totals = [
            'total_amount' => $payments->sum('amount'),
        ];

        return view('reports.payments', compact('payments', 'totals'));
    }

    public function trialBalance(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $totals = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) use ($asOfDate) {
                        $q->where('status', 'APPROVED')
                          ->where('date', '<=', $asOfDate);
                    })
                    ->selectRaw('COALESCE(SUM(debit_amount), 0) as total_debit, COALESCE(SUM(credit_amount), 0) as total_credit')
                    ->first();

                $account->total_debit = $totals->total_debit;
                $account->total_credit = $totals->total_credit;
                $account->balance = $totals->total_debit - $totals->total_credit;

                return $account;
            })
            ->filter(fn ($account) => $account->total_debit != 0 || $account->total_credit != 0);

        $totalDebits = $accounts->sum('total_debit');
        $totalCredits = $accounts->sum('total_credit');

        return view('reports.trial-balance', compact('accounts', 'totalDebits', 'totalCredits', 'asOfDate'));
    }

    public function profitAndLoss(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $incomeAccounts = $this->getAccountBalances($companyId, 'INCOME', $fromDate, $toDate);
        $expenseAccounts = $this->getAccountBalances($companyId, 'EXPENSE', $fromDate, $toDate);

        $totalIncome = $incomeAccounts->sum(fn ($a) => $a->total_credit - $a->total_debit);
        $totalExpenses = $expenseAccounts->sum(fn ($a) => $a->total_debit - $a->total_credit);
        $netProfit = $totalIncome - $totalExpenses;

        return view('reports.profit-and-loss', compact(
            'incomeAccounts', 'expenseAccounts', 'totalIncome', 'totalExpenses', 'netProfit', 'fromDate', 'toDate'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $assetAccounts = $this->getAccountBalances($companyId, 'ASSET', null, $asOfDate);
        $liabilityAccounts = $this->getAccountBalances($companyId, 'LIABILITY', null, $asOfDate);
        $equityAccounts = $this->getAccountBalances($companyId, 'EQUITY', null, $asOfDate);

        $totalAssets = $assetAccounts->sum(fn ($a) => $a->total_debit - $a->total_credit);
        $totalLiabilities = $liabilityAccounts->sum(fn ($a) => $a->total_credit - $a->total_debit);
        $totalEquity = $equityAccounts->sum(fn ($a) => $a->total_credit - $a->total_debit);

        return view('reports.balance-sheet', compact(
            'assetAccounts', 'liabilityAccounts', 'equityAccounts',
            'totalAssets', 'totalLiabilities', 'totalEquity', 'asOfDate'
        ));
    }

    public function cashFlow(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $receipts = Receipt::where('company_id', $companyId)
            ->where('status', 'APPROVED')
            ->whereBetween('date', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as period, SUM(amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $payments = Payment::where('company_id', $companyId)
            ->where('status', 'APPROVED')
            ->whereBetween('date', [$fromDate, $toDate])
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as period, SUM(amount) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $periods = $receipts->keys()->merge($payments->keys())->unique()->sort();

        $cashFlow = $periods->map(fn ($period) => [
            'period' => $period,
            'receipts' => $receipts->get($period, 0),
            'payments' => $payments->get($period, 0),
            'net' => $receipts->get($period, 0) - $payments->get($period, 0),
        ]);

        return view('reports.cash-flow', compact('cashFlow', 'fromDate', 'toDate'));
    }

    public function customerAging(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOfDate = Carbon::parse($request->input('as_of_date', now()->toDateString()));

        $invoices = SalesInvoice::where('company_id', $companyId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
            ->with('customer')
            ->get();

        $aging = $invoices->groupBy('customer_id')->map(function ($customerInvoices) use ($asOfDate) {
            $customer = $customerInvoices->first()->customer;
            $buckets = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];

            foreach ($customerInvoices as $invoice) {
                $dueDate = Carbon::parse($invoice->due_date ?? $invoice->date);
                $daysOverdue = $dueDate->diffInDays($asOfDate, false);

                if ($daysOverdue <= 30) {
                    $buckets['0_30'] += $invoice->balance_amount;
                } elseif ($daysOverdue <= 60) {
                    $buckets['31_60'] += $invoice->balance_amount;
                } elseif ($daysOverdue <= 90) {
                    $buckets['61_90'] += $invoice->balance_amount;
                } else {
                    $buckets['90_plus'] += $invoice->balance_amount;
                }
            }

            return [
                'customer' => $customer,
                'buckets' => $buckets,
                'total' => array_sum($buckets),
            ];
        })->values();

        return view('reports.customer-aging', compact('aging', 'asOfDate'));
    }

    public function supplierAging(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $asOfDate = Carbon::parse($request->input('as_of_date', now()->toDateString()));

        $invoices = PurchaseInvoice::where('company_id', $companyId)
            ->where('balance_amount', '>', 0)
            ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
            ->with('supplier')
            ->get();

        $aging = $invoices->groupBy('supplier_id')->map(function ($supplierInvoices) use ($asOfDate) {
            $supplier = $supplierInvoices->first()->supplier;
            $buckets = ['0_30' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];

            foreach ($supplierInvoices as $invoice) {
                $dueDate = Carbon::parse($invoice->due_date ?? $invoice->date);
                $daysOverdue = $dueDate->diffInDays($asOfDate, false);

                if ($daysOverdue <= 30) {
                    $buckets['0_30'] += $invoice->balance_amount;
                } elseif ($daysOverdue <= 60) {
                    $buckets['31_60'] += $invoice->balance_amount;
                } elseif ($daysOverdue <= 90) {
                    $buckets['61_90'] += $invoice->balance_amount;
                } else {
                    $buckets['90_plus'] += $invoice->balance_amount;
                }
            }

            return [
                'supplier' => $supplier,
                'buckets' => $buckets,
                'total' => array_sum($buckets),
            ];
        })->values();

        return view('reports.supplier-aging', compact('aging', 'asOfDate'));
    }

    public function generalLedger(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
        ]);

        $account = ChartOfAccount::where('id', $request->account_id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $lines = JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->where('status', 'APPROVED')
                  ->whereBetween('date', [$fromDate, $toDate]);
            })
            ->with('journalEntry')
            ->get()
            ->sortBy('journalEntry.date');

        // Opening balance
        $openingBalance = JournalLine::where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($fromDate) {
                $q->where('status', 'APPROVED')
                  ->where('date', '<', $fromDate);
            })
            ->selectRaw('COALESCE(SUM(debit_amount), 0) - COALESCE(SUM(credit_amount), 0) as balance')
            ->value('balance') ?? 0;

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('reports.general-ledger', compact('account', 'lines', 'openingBalance', 'fromDate', 'toDate', 'accounts'));
    }

    public function dayBook(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $date = $request->input('date', now()->toDateString());

        $salesInvoices = SalesInvoice::where('company_id', $companyId)->where('date', $date)->with('customer')->get();
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)->where('date', $date)->with('supplier')->get();
        $receipts = Receipt::where('company_id', $companyId)->where('date', $date)->with('customer')->get();
        $payments = Payment::where('company_id', $companyId)->where('date', $date)->with('supplier')->get();

        return view('reports.day-book', compact('salesInvoices', 'purchaseInvoices', 'receipts', 'payments', 'date'));
    }

    public function vatReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $fromDate = $request->input('from_date', now()->startOfYear()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $salesTax = SalesInvoice::where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED')
            ->whereBetween('date', [$fromDate, $toDate])
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_sales, COALESCE(SUM(tax_amount), 0) as output_tax')
            ->first();

        $purchaseTax = PurchaseInvoice::where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED')
            ->whereBetween('date', [$fromDate, $toDate])
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_purchases, COALESCE(SUM(tax_amount), 0) as input_tax')
            ->first();

        $netTax = $salesTax->output_tax - $purchaseTax->input_tax;

        return view('reports.vat', compact('salesTax', 'purchaseTax', 'netTax', 'fromDate', 'toDate'));
    }

    public function employeeReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)
            ->when($request->department_id, fn ($q, $v) => $q->where('department_id', $v))
            ->when($request->designation_id, fn ($q, $v) => $q->where('designation_id', $v))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with(['department', 'designation', 'branch'])
            ->orderBy('full_name')
            ->paginate(50);

        return view('reports.employees', compact('employees'));
    }

    public function attendanceReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->get();

        $attendance = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');

        $summary = $employees->map(function ($employee) use ($attendance) {
            $records = $attendance->get($employee->id, collect());

            return [
                'employee' => $employee,
                'present' => $records->where('status', 'PRESENT')->count(),
                'absent' => $records->where('status', 'ABSENT')->count(),
                'half_day' => $records->where('status', 'HALF_DAY')->count(),
                'leave' => $records->where('status', 'LEAVE')->count(),
                'late' => $records->where('status', 'LATE')->count(),
                'overtime_hours' => $records->sum('overtime_hours'),
            ];
        });

        return view('reports.attendance', compact('summary', 'month', 'year'));
    }

    public function payrollReport(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $payrolls = Payroll::where('company_id', $companyId)
            ->where('month', $month)
            ->where('year', $year)
            ->with('employee')
            ->get();

        $totals = [
            'basic_salary' => $payrolls->sum('basic_salary'),
            'total_earnings' => $payrolls->sum('total_earnings'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'overtime_amount' => $payrolls->sum('overtime_amount'),
            'bonus' => $payrolls->sum('bonus'),
            'loan_deduction' => $payrolls->sum('loan_deduction'),
            'net_salary' => $payrolls->sum('net_salary'),
        ];

        return view('reports.payroll', compact('payrolls', 'totals', 'month', 'year'));
    }

    protected function getAccountBalances(int $companyId, string $type, ?string $fromDate, string $toDate)
    {
        return ChartOfAccount::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_group', false)
            ->where('is_active', true)
            ->get()
            ->map(function ($account) use ($fromDate, $toDate) {
                $query = JournalLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                        $q->where('status', 'APPROVED');
                        if ($fromDate) {
                            $q->whereBetween('date', [$fromDate, $toDate]);
                        } else {
                            $q->where('date', '<=', $toDate);
                        }
                    });

                $totals = $query->selectRaw('COALESCE(SUM(debit_amount), 0) as total_debit, COALESCE(SUM(credit_amount), 0) as total_credit')
                    ->first();

                $account->total_debit = $totals->total_debit;
                $account->total_credit = $totals->total_credit;

                return $account;
            })
            ->filter(fn ($account) => $account->total_debit != 0 || $account->total_credit != 0);
    }
}
