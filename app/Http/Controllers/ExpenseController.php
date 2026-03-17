<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\CostCenter;
use App\Models\Project;
use App\Models\TaxRate;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $expenses = Expense::where('company_id', $companyId)
            ->when($request->category, fn($q, $category) => $q->where('category', $category))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('expense_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('expense_date', '<=', $dateTo))
            ->when($request->branch_id, fn($q, $branchId) => $q->where('branch_id', $branchId))
            ->with('expenseAccount', 'paymentAccount', 'branch')
            ->latest()
            ->paginate(20);

        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('expenses.index', compact('expenses', 'branches'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $expenseAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'EXPENSE')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        $bankAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->whereIn('sub_type', ['BANK', 'CASH'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();

        return view('expenses.create', compact(
            'expenseAccounts', 'bankAccounts', 'currencies', 'branches', 'costCenters', 'projects', 'taxRates'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date' => 'required|date',
            'expense_account_id' => 'required|exists:chart_of_accounts,id',
            'payment_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'description' => 'required|string|max:500',
        ]);

        $companyId = Auth::user()->company_id;

        $expenseNumber = 'EXP-' . str_pad(
            Expense::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $taxAmount = 0;
        if ($request->tax_rate_id) {
            $taxRate = TaxRate::find($request->tax_rate_id);
            $taxAmount = $request->amount * ($taxRate->rate / 100);
        }

        DB::beginTransaction();
        try {
            $expense = Expense::create([
                'company_id' => $companyId,
                'expense_number' => $expenseNumber,
                'expense_date' => $request->expense_date,
                'expense_account_id' => $request->expense_account_id,
                'payment_account_id' => $request->payment_account_id,
                'amount' => $request->amount,
                'tax_rate_id' => $request->tax_rate_id,
                'tax_amount' => $taxAmount,
                'total' => $request->amount + $taxAmount,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'cost_center_id' => $request->cost_center_id,
                'project_id' => $request->project_id,
                'category' => $request->category,
                'description' => $request->description,
                'reference' => $request->reference,
                'status' => 'POSTED',
                'created_by' => Auth::id(),
            ]);

            $this->createExpenseJournalEntry($expense);

            DB::commit();
            return redirect()->route('expenses.show', $expense)->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create expense: ' . $e->getMessage());
        }
    }

    public function show(Expense $expense)
    {
        $this->authorizeCompany($expense);
        $expense->load('expenseAccount', 'paymentAccount', 'branch', 'costCenter', 'project', 'taxRate');

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $this->authorizeCompany($expense);
        $companyId = Auth::user()->company_id;

        $expenseAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'EXPENSE')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        $bankAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->whereIn('sub_type', ['BANK', 'CASH'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();

        return view('expenses.edit', compact(
            'expense', 'expenseAccounts', 'bankAccounts', 'currencies', 'branches', 'costCenters', 'projects', 'taxRates'
        ));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizeCompany($expense);

        $request->validate([
            'expense_date' => 'required|date',
            'expense_account_id' => 'required|exists:chart_of_accounts,id',
            'payment_account_id' => 'required|exists:chart_of_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'description' => 'required|string|max:500',
        ]);

        $taxAmount = 0;
        if ($request->tax_rate_id) {
            $taxRate = TaxRate::find($request->tax_rate_id);
            $taxAmount = $request->amount * ($taxRate->rate / 100);
        }

        DB::beginTransaction();
        try {
            $expense->update([
                'expense_date' => $request->expense_date,
                'expense_account_id' => $request->expense_account_id,
                'payment_account_id' => $request->payment_account_id,
                'amount' => $request->amount,
                'tax_rate_id' => $request->tax_rate_id,
                'tax_amount' => $taxAmount,
                'total' => $request->amount + $taxAmount,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'cost_center_id' => $request->cost_center_id,
                'project_id' => $request->project_id,
                'category' => $request->category,
                'description' => $request->description,
                'reference' => $request->reference,
            ]);

            DB::commit();
            return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeCompany($expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    private function createExpenseJournalEntry(Expense $expense)
    {
        $companyId = $expense->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $expense->expense_date,
            'reference' => $expense->expense_number,
            'description' => 'Auto journal entry for Expense ' . $expense->expense_number,
            'source_type' => 'Expense',
            'source_id' => $expense->id,
            'is_auto_generated' => true,
            'currency_id' => $expense->currency_id,
            'exchange_rate' => $expense->exchange_rate,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Expense Account
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $expense->expense_account_id,
            'description' => 'Expense - ' . $expense->expense_number,
            'debit_amount' => $expense->amount,
            'credit_amount' => 0,
            'cost_center_id' => $expense->cost_center_id,
            'project_id' => $expense->project_id,
        ]);

        // Dr: Input Tax (if applicable)
        if ($expense->tax_amount > 0) {
            $taxAccountId = ChartOfAccount::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('code', 'like', '%input_tax%')->orWhere('name', 'like', '%Input Tax%');
                })
                ->value('id');

            if ($taxAccountId) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccountId,
                    'description' => 'Input tax on expense - ' . $expense->expense_number,
                    'debit_amount' => $expense->tax_amount,
                    'credit_amount' => 0,
                    'cost_center_id' => $expense->cost_center_id,
                    'project_id' => $expense->project_id,
                ]);
            }
        }

        // Cr: Cash/Bank Account
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $expense->payment_account_id,
            'description' => 'Payment for expense - ' . $expense->expense_number,
            'debit_amount' => 0,
            'credit_amount' => $expense->total,
            'cost_center_id' => $expense->cost_center_id,
            'project_id' => $expense->project_id,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
