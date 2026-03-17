<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Currency;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->with(['currency', 'account'])
            ->orderBy('bank_name')
            ->paginate(20);

        return view('bank-accounts.index', compact('bankAccounts'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->where('type', 'ASSET')
            ->orderBy('code')
            ->get();

        return view('bank-accounts.create', compact('currencies', 'accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'account_holder_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:100',
            'currency_id' => 'required|exists:currencies,id',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        BankAccount::create($validated);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    public function show(BankAccount $bankAccount)
    {
        $this->authorizeCompany($bankAccount);
        $bankAccount->load(['currency', 'account']);

        return view('bank-accounts.show', compact('bankAccount'));
    }

    public function edit(BankAccount $bankAccount)
    {
        $this->authorizeCompany($bankAccount);
        $companyId = auth()->user()->company_id;

        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->where('type', 'ASSET')
            ->orderBy('code')
            ->get();

        return view('bank-accounts.edit', compact('bankAccount', 'currencies', 'accounts'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $this->authorizeCompany($bankAccount);

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
            'account_holder_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:100',
            'currency_id' => 'required|exists:currencies,id',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $bankAccount->update($validated);

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $this->authorizeCompany($bankAccount);
        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
