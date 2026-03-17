<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\TaxRate;
use App\Models\Warehouse;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Item::where('company_id', $companyId)
            ->with(['category', 'taxRate', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->input('item_type'));
        }

        $items = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $categories = ItemCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxRates = TaxRate::whereHas('taxProfile', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('is_active', true);
        })->where('is_active', true)->get();

        $warehouses = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('items.create', compact('categories', 'taxRates', 'warehouses', 'accounts'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:items,code,NULL,id,company_id,' . $companyId,
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit' => 'nullable|string|max:50',
            'item_type' => 'required|in:STOCK,NON_STOCK,SERVICE,CONSUMABLE,FIXED_ASSET',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sales_rate' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'purchase_account_id' => 'nullable|exists:chart_of_accounts,id',
            'sales_account_id' => 'nullable|exists:chart_of_accounts,id',
            'inventory_account_id' => 'nullable|exists:chart_of_accounts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $this->authorizeCompany($item);
        $item->load(['category', 'taxRate', 'warehouse']);

        return view('items.show', compact('item'));
    }

    public function edit(Item $item)
    {
        $this->authorizeCompany($item);
        $companyId = auth()->user()->company_id;

        $categories = ItemCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $taxRates = TaxRate::whereHas('taxProfile', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('is_active', true);
        })->where('is_active', true)->get();

        $warehouses = Warehouse::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_group', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('items.edit', compact('item', 'categories', 'taxRates', 'warehouses', 'accounts'));
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeCompany($item);
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:items,code,' . $item->id . ',id,company_id,' . $companyId,
            'barcode' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:item_categories,id',
            'unit' => 'nullable|string|max:50',
            'item_type' => 'required|in:STOCK,NON_STOCK,SERVICE,CONSUMABLE,FIXED_ASSET',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sales_rate' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'minimum_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'purchase_account_id' => 'nullable|exists:chart_of_accounts,id',
            'sales_account_id' => 'nullable|exists:chart_of_accounts,id',
            'inventory_account_id' => 'nullable|exists:chart_of_accounts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $this->authorizeCompany($item);
        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
