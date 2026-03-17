<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->with('parent')
            ->orderBy('code')
            ->get();

        // Group by type for tree structure display
        $groupedAccounts = $accounts->groupBy('type');

        return view('chart-of-accounts.index', compact('accounts', 'groupedAccounts'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.create', compact('parentAccounts'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:chart_of_accounts,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,INCOME,EXPENSE',
            'sub_type' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_group' => 'boolean',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['company_id'] = $companyId;

        ChartOfAccount::create($validated);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(ChartOfAccount $chartOfAccount)
    {
        $this->authorizeCompany($chartOfAccount);
        $chartOfAccount->load('parent', 'children');

        return view('chart-of-accounts.show', compact('chartOfAccount'));
    }

    public function edit(ChartOfAccount $chartOfAccount)
    {
        $this->authorizeCompany($chartOfAccount);
        $companyId = auth()->user()->company_id;

        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', true)
            ->where('is_active', true)
            ->where('id', '!=', $chartOfAccount->id)
            ->orderBy('code')
            ->get();

        return view('chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts'));
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $this->authorizeCompany($chartOfAccount);
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:chart_of_accounts,code,' . $chartOfAccount->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'type' => 'required|in:ASSET,LIABILITY,EQUITY,INCOME,EXPENSE',
            'sub_type' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_group' => 'boolean',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $chartOfAccount->update($validated);

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $this->authorizeCompany($chartOfAccount);

        if ($chartOfAccount->is_system) {
            return redirect()->route('chart-of-accounts.index')
                ->with('error', 'System accounts cannot be deleted.');
        }

        $chartOfAccount->delete();

        return redirect()->route('chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
    }

    public function ledger(ChartOfAccount $chartOfAccount, Request $request)
    {
        $this->authorizeCompany($chartOfAccount);
        $companyId = auth()->user()->company_id;

        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        // Query journal lines for this account (if journal_entries/journal_lines tables exist)
        $journalLines = collect();

        try {
            $journalLines = DB::table('journal_lines')
                ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_lines.account_id', $chartOfAccount->id)
                ->where('journal_entries.company_id', $companyId)
                ->whereDate('journal_entries.date', '>=', $fromDate)
                ->whereDate('journal_entries.date', '<=', $toDate)
                ->orderBy('journal_entries.date')
                ->orderBy('journal_entries.id')
                ->select(
                    'journal_entries.date',
                    'journal_entries.reference',
                    'journal_entries.description',
                    'journal_lines.debit',
                    'journal_lines.credit',
                    'journal_lines.description as line_description'
                )
                ->paginate(50)
                ->withQueryString();
        } catch (\Exception $e) {
            // Journal tables may not exist yet
        }

        return view('chart-of-accounts.ledger', compact(
            'chartOfAccount',
            'journalLines',
            'fromDate',
            'toDate'
        ));
    }

    public function trialBalance(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $asOfDate = $request->input('as_of_date', now()->toDateString());

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Try to compute balances from journal lines
        $balances = collect();

        try {
            $balances = DB::table('journal_lines')
                ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.company_id', $companyId)
                ->whereDate('journal_entries.date', '<=', $asOfDate)
                ->groupBy('journal_lines.account_id')
                ->select(
                    'journal_lines.account_id',
                    DB::raw('SUM(journal_lines.debit) as total_debit'),
                    DB::raw('SUM(journal_lines.credit) as total_credit')
                )
                ->get()
                ->keyBy('account_id');
        } catch (\Exception $e) {
            // Journal tables may not exist yet
        }

        return view('chart-of-accounts.trial-balance', compact('accounts', 'balances', 'asOfDate'));
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
