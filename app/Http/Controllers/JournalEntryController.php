<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use App\Models\CostCenter;
use App\Models\Project;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $journalEntries = JournalEntry::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('entry_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('entry_date', '<=', $dateTo))
            ->when($request->is_auto_generated !== null, fn($q) => $q->where('is_auto_generated', $request->is_auto_generated))
            ->latest()
            ->paginate(20);

        return view('journal-entries.index', compact('journalEntries'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $accounts = ChartOfAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();

        return view('journal-entries.create', compact('accounts', 'costCenters', 'projects', 'currencies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit_amount' => 'nullable|numeric|min:0',
            'lines.*.credit_amount' => 'nullable|numeric|min:0',
        ]);

        // Validate total debits = total credits
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($request->lines as $line) {
            $totalDebits += $line['debit_amount'] ?? 0;
            $totalCredits += $line['credit_amount'] ?? 0;
        }

        if (round($totalDebits, 2) !== round($totalCredits, 2)) {
            return back()->withInput()->with('error', 'Total debits must equal total credits. Debits: ' . number_format($totalDebits, 2) . ', Credits: ' . number_format($totalCredits, 2));
        }

        $companyId = Auth::user()->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => $entryNumber,
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'source_type' => $request->source_type,
                'source_id' => $request->source_id,
                'is_auto_generated' => false,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                    'project_id' => $line['project_id'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('journal-entries.show', $journalEntry)->with('success', 'Journal Entry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create journal entry: ' . $e->getMessage());
        }
    }

    public function show(JournalEntry $journalEntry)
    {
        $this->authorizeCompany($journalEntry);
        $journalEntry->load('lines.account', 'lines.costCenter', 'lines.project', 'currency');

        return view('journal-entries.show', compact('journalEntry'));
    }

    public function edit(JournalEntry $journalEntry)
    {
        $this->authorizeCompany($journalEntry);

        if ($journalEntry->is_auto_generated) {
            return back()->with('error', 'Auto-generated journal entries cannot be edited.');
        }

        $companyId = Auth::user()->company_id;

        $journalEntry->load('lines');
        $accounts = ChartOfAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('code')->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();

        return view('journal-entries.edit', compact('journalEntry', 'accounts', 'costCenters', 'projects', 'currencies'));
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        $this->authorizeCompany($journalEntry);

        if ($journalEntry->is_auto_generated) {
            return back()->with('error', 'Auto-generated journal entries cannot be edited.');
        }

        $request->validate([
            'entry_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'description' => 'required|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit_amount' => 'nullable|numeric|min:0',
            'lines.*.credit_amount' => 'nullable|numeric|min:0',
        ]);

        // Validate total debits = total credits
        $totalDebits = 0;
        $totalCredits = 0;

        foreach ($request->lines as $line) {
            $totalDebits += $line['debit_amount'] ?? 0;
            $totalCredits += $line['credit_amount'] ?? 0;
        }

        if (round($totalDebits, 2) !== round($totalCredits, 2)) {
            return back()->withInput()->with('error', 'Total debits must equal total credits. Debits: ' . number_format($totalDebits, 2) . ', Credits: ' . number_format($totalCredits, 2));
        }

        DB::beginTransaction();
        try {
            $journalEntry->update([
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
            ]);

            $journalEntry->lines()->delete();

            foreach ($request->lines as $line) {
                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                    'project_id' => $line['project_id'] ?? null,
                ]);
            }

            DB::commit();
            return redirect()->route('journal-entries.show', $journalEntry)->with('success', 'Journal Entry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update journal entry: ' . $e->getMessage());
        }
    }

    public function destroy(JournalEntry $journalEntry)
    {
        $this->authorizeCompany($journalEntry);

        if ($journalEntry->is_auto_generated) {
            return back()->with('error', 'Auto-generated journal entries cannot be deleted.');
        }

        $journalEntry->lines()->delete();
        $journalEntry->delete();

        return redirect()->route('journal-entries.index')->with('success', 'Journal Entry deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
