<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $reconciliations = BankReconciliation::whereHas('bankAccount', fn ($q) => $q->where('company_id', $companyId))
            ->with('bankAccount')
            ->latest()
            ->paginate(25);

        return view('bank-reconciliations.index', compact('reconciliations'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return view('bank-reconciliations.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'statement_balance' => 'required|numeric',
            'book_balance' => 'required|numeric',
            'status' => 'nullable|in:DRAFT,COMPLETED',
        ]);

        // Verify bank account belongs to company
        BankAccount::where('id', $validated['bank_account_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $validated['difference'] = $validated['statement_balance'] - $validated['book_balance'];
        $validated['status'] = $validated['status'] ?? 'DRAFT';
        $validated['reconciled_by'] = auth()->id();

        BankReconciliation::create($validated);

        return redirect()->route('bank-reconciliations.index')->with('success', 'Bank reconciliation created successfully.');
    }

    public function show(BankReconciliation $bankReconciliation)
    {
        $this->authorizeCompany($bankReconciliation);

        $bankReconciliation->load('bankAccount');

        return view('bank-reconciliations.show', compact('bankReconciliation'));
    }

    public function edit(BankReconciliation $bankReconciliation)
    {
        $this->authorizeCompany($bankReconciliation);

        $companyId = auth()->user()->company_id;

        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        return view('bank-reconciliations.edit', compact('bankReconciliation', 'bankAccounts'));
    }

    public function update(Request $request, BankReconciliation $bankReconciliation)
    {
        $this->authorizeCompany($bankReconciliation);

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'statement_balance' => 'required|numeric',
            'book_balance' => 'required|numeric',
            'status' => 'required|in:DRAFT,COMPLETED',
        ]);

        $validated['difference'] = $validated['statement_balance'] - $validated['book_balance'];

        $bankReconciliation->update($validated);

        return redirect()->route('bank-reconciliations.index')->with('success', 'Bank reconciliation updated successfully.');
    }

    public function destroy(BankReconciliation $bankReconciliation)
    {
        $this->authorizeCompany($bankReconciliation);

        $bankReconciliation->delete();

        return redirect()->route('bank-reconciliations.index')->with('success', 'Bank reconciliation deleted successfully.');
    }

    protected function authorizeCompany(BankReconciliation $reconciliation): void
    {
        $reconciliation->loadMissing('bankAccount');

        if ($reconciliation->bankAccount->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
