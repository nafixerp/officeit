<?php

namespace App\Http\Controllers;

use App\Models\ContraEntry;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContraEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $contraEntries = ContraEntry::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('entry_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('entry_date', '<=', $dateTo))
            ->with('fromAccount', 'toAccount')
            ->latest()
            ->paginate(20);

        return view('contra-entries.index', compact('contraEntries'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', 'ASSET')
            ->whereIn('sub_type', ['BANK', 'CASH'])
            ->orderBy('code')
            ->get();

        return view('contra-entries.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $companyId = Auth::user()->company_id;

        $entryNumber = 'CE-' . str_pad(
            ContraEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $contraEntry = ContraEntry::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'entry_date' => $request->entry_date,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'reference' => $request->reference,
                'status' => 'POSTED',
                'created_by' => Auth::id(),
            ]);

            $this->createContraJournalEntry($contraEntry);

            DB::commit();
            return redirect()->route('contra-entries.show', $contraEntry)->with('success', 'Contra Entry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create contra entry: ' . $e->getMessage());
        }
    }

    public function show(ContraEntry $contraEntry)
    {
        $this->authorizeCompany($contraEntry);
        $contraEntry->load('fromAccount', 'toAccount');

        return view('contra-entries.show', compact('contraEntry'));
    }

    public function edit(ContraEntry $contraEntry)
    {
        $this->authorizeCompany($contraEntry);
        $companyId = Auth::user()->company_id;

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('type', 'ASSET')
            ->whereIn('sub_type', ['BANK', 'CASH'])
            ->orderBy('code')
            ->get();

        return view('contra-entries.edit', compact('contraEntry', 'accounts'));
    }

    public function update(Request $request, ContraEntry $contraEntry)
    {
        $this->authorizeCompany($contraEntry);

        $request->validate([
            'entry_date' => 'required|date',
            'from_account_id' => 'required|exists:chart_of_accounts,id',
            'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $contraEntry->update([
                'entry_date' => $request->entry_date,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'reference' => $request->reference,
            ]);

            DB::commit();
            return redirect()->route('contra-entries.show', $contraEntry)->with('success', 'Contra Entry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update contra entry: ' . $e->getMessage());
        }
    }

    public function destroy(ContraEntry $contraEntry)
    {
        $this->authorizeCompany($contraEntry);

        $contraEntry->delete();

        return redirect()->route('contra-entries.index')->with('success', 'Contra Entry deleted successfully.');
    }

    private function createContraJournalEntry(ContraEntry $contraEntry)
    {
        $companyId = $contraEntry->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $contraEntry->entry_date,
            'reference' => $contraEntry->entry_number,
            'description' => 'Auto journal entry for Contra Entry ' . $contraEntry->entry_number,
            'source_type' => 'ContraEntry',
            'source_id' => $contraEntry->id,
            'is_auto_generated' => true,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: To Account (receiving funds)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $contraEntry->to_account_id,
            'description' => 'Fund transfer in - ' . $contraEntry->entry_number,
            'debit_amount' => $contraEntry->amount,
            'credit_amount' => 0,
        ]);

        // Cr: From Account (sending funds)
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $contraEntry->from_account_id,
            'description' => 'Fund transfer out - ' . $contraEntry->entry_number,
            'debit_amount' => 0,
            'credit_amount' => $contraEntry->amount,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
