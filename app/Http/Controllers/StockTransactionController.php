<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $stockTransactions = StockTransaction::where('company_id', $companyId)
            ->when($request->transaction_type, fn($q, $type) => $q->where('transaction_type', $type))
            ->when($request->item_id, fn($q, $itemId) => $q->where('item_id', $itemId))
            ->when($request->warehouse_id, fn($q, $warehouseId) => $q->where('warehouse_id', $warehouseId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('transaction_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('transaction_date', '<=', $dateTo))
            ->with('item', 'warehouse')
            ->latest()
            ->paginate(20);

        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->get();

        return view('stock-transactions.index', compact('stockTransactions', 'items', 'warehouses'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->get();

        return view('stock-transactions.create', compact('items', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:IN,OUT,TRANSFER,ADJUSTMENT',
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id|different:warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $companyId = Auth::user()->company_id;

        $transactionNumber = 'STK-' . str_pad(
            StockTransaction::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $stockTransaction = StockTransaction::create([
                'company_id' => $companyId,
                'transaction_number' => $transactionNumber,
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'item_id' => $request->item_id,
                'warehouse_id' => $request->warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost ?? 0,
                'total_cost' => $request->quantity * ($request->unit_cost ?? 0),
                'reference' => $request->reference,
                'source_type' => $request->source_type,
                'source_id' => $request->source_id,
                'description' => $request->description,
                'status' => 'POSTED',
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return redirect()->route('stock-transactions.show', $stockTransaction)->with('success', 'Stock Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create stock transaction: ' . $e->getMessage());
        }
    }

    public function show(StockTransaction $stockTransaction)
    {
        $this->authorizeCompany($stockTransaction);
        $stockTransaction->load('item', 'warehouse', 'toWarehouse');

        return view('stock-transactions.show', compact('stockTransaction'));
    }

    public function edit(StockTransaction $stockTransaction)
    {
        $this->authorizeCompany($stockTransaction);
        $companyId = Auth::user()->company_id;

        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $warehouses = Warehouse::where('company_id', $companyId)->where('is_active', true)->get();

        return view('stock-transactions.edit', compact('stockTransaction', 'items', 'warehouses'));
    }

    public function update(Request $request, StockTransaction $stockTransaction)
    {
        $this->authorizeCompany($stockTransaction);

        $request->validate([
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:IN,OUT,TRANSFER,ADJUSTMENT',
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id|different:warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $stockTransaction->update([
                'transaction_date' => $request->transaction_date,
                'transaction_type' => $request->transaction_type,
                'item_id' => $request->item_id,
                'warehouse_id' => $request->warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost ?? 0,
                'total_cost' => $request->quantity * ($request->unit_cost ?? 0),
                'reference' => $request->reference,
                'description' => $request->description,
            ]);

            DB::commit();
            return redirect()->route('stock-transactions.show', $stockTransaction)->with('success', 'Stock Transaction updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update stock transaction: ' . $e->getMessage());
        }
    }

    public function destroy(StockTransaction $stockTransaction)
    {
        $this->authorizeCompany($stockTransaction);

        $stockTransaction->delete();

        return redirect()->route('stock-transactions.index')->with('success', 'Stock Transaction deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
