<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Branch;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $assets = Asset::where('company_id', $companyId)
            ->when($request->category, fn ($q, $v) => $q->where('category', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', "%{$v}%")
                  ->orWhere('asset_code', 'like', "%{$v}%");
            }))
            ->with(['assignedTo', 'branch'])
            ->latest()
            ->paginate(25);

        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        $categories = Asset::where('company_id', $companyId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('assets.index', compact('assets', 'branches', 'categories'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('assets.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_value' => 'required|numeric|min:0',
            'depreciation_method' => 'nullable|in:STRAIGHT_LINE,REDUCING_BALANCE,NONE',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'nullable|in:ACTIVE,DISPOSED,UNDER_MAINTENANCE',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['current_value'] = $validated['purchase_value'];
        $validated['accumulated_depreciation'] = 0;
        $validated['status'] = $validated['status'] ?? 'ACTIVE';

        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $this->authorizeCompany($asset);

        $asset->load(['assignedTo', 'branch']);

        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $this->authorizeCompany($asset);

        $companyId = auth()->user()->company_id;
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('assets.edit', compact('asset', 'branches'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorizeCompany($asset);

        $validated = $request->validate([
            'asset_code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_value' => 'required|numeric|min:0',
            'depreciation_method' => 'nullable|in:STRAIGHT_LINE,REDUCING_BALANCE,NONE',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:ACTIVE,DISPOSED,UNDER_MAINTENANCE',
            'disposal_date' => 'nullable|date',
            'disposal_value' => 'nullable|numeric|min:0',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $this->authorizeCompany($asset);

        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function depreciate(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
            'date' => 'required|date',
        ]);

        $assets = Asset::where('company_id', $companyId)
            ->whereIn('id', $validated['asset_ids'])
            ->where('status', 'ACTIVE')
            ->where('depreciation_method', '!=', 'NONE')
            ->where('current_value', '>', 0)
            ->get();

        // Find depreciation expense and accumulated depreciation accounts
        $depreciationExpenseAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'EXPENSE')
            ->where(function ($q) {
                $q->where('name', 'like', '%Depreciation%')
                  ->orWhere('sub_type', 'depreciation_expense');
            })
            ->first();

        $accumulatedDepreciationAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->where(function ($q) {
                $q->where('name', 'like', '%Accumulated Depreciation%')
                  ->orWhere('sub_type', 'accumulated_depreciation');
            })
            ->first();

        $totalDepreciation = 0;

        DB::transaction(function () use ($assets, $validated, $companyId, $depreciationExpenseAccount, $accumulatedDepreciationAccount, &$totalDepreciation) {
            foreach ($assets as $asset) {
                $depreciationAmount = 0;

                if ($asset->depreciation_method === 'STRAIGHT_LINE') {
                    // Annual depreciation = purchase_value * rate / 100, monthly = annual / 12
                    $depreciationAmount = round(($asset->purchase_value * $asset->depreciation_rate / 100) / 12, 2);
                } elseif ($asset->depreciation_method === 'REDUCING_BALANCE') {
                    // Monthly depreciation on current value
                    $depreciationAmount = round(($asset->current_value * $asset->depreciation_rate / 100) / 12, 2);
                }

                // Ensure we don't depreciate below zero
                $depreciationAmount = min($depreciationAmount, $asset->current_value);

                if ($depreciationAmount > 0) {
                    $asset->update([
                        'accumulated_depreciation' => $asset->accumulated_depreciation + $depreciationAmount,
                        'current_value' => $asset->current_value - $depreciationAmount,
                    ]);

                    $totalDepreciation += $depreciationAmount;
                }
            }

            // Create journal entry for total depreciation
            if ($totalDepreciation > 0 && $depreciationExpenseAccount && $accumulatedDepreciationAccount) {
                $journalEntry = JournalEntry::create([
                    'company_id' => $companyId,
                    'journal_number' => 'JV-DEP-' . now()->format('Ymd') . '-' . uniqid(),
                    'date' => $validated['date'],
                    'total_amount' => $totalDepreciation,
                    'narration' => 'Asset depreciation for ' . $validated['date'],
                    'type' => 'DEPRECIATION',
                    'status' => 'APPROVED',
                    'is_auto_generated' => true,
                    'source_type' => 'Asset',
                    'created_by' => auth()->id(),
                ]);

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $depreciationExpenseAccount->id,
                    'debit_amount' => $totalDepreciation,
                    'credit_amount' => 0,
                    'narration' => 'Depreciation expense',
                ]);

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accumulatedDepreciationAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $totalDepreciation,
                    'narration' => 'Accumulated depreciation',
                ]);
            }
        });

        return back()->with('success', "Depreciation of " . number_format($totalDepreciation, 2) . " calculated and posted for {$assets->count()} assets.");
    }

    protected function authorizeCompany(Asset $asset): void
    {
        if ($asset->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
