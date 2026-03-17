<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Service;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $purchaseOrders = PurchaseOrder::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->with('supplier')
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();

        return view('purchase-orders.create', compact('suppliers', 'items', 'services', 'currencies', 'branches', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $orderNumber = 'PO-' . str_pad(
            PurchaseOrder::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $taxAmount += $item['tax_amount'] ?? 0;
            }

            $purchaseOrder = PurchaseOrder::create([
                'company_id' => $companyId,
                'order_number' => $orderNumber,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'project_id' => $request->project_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total' => $subtotal + $taxAmount - ($request->discount_amount ?? 0),
                'notes' => $request->notes,
                'terms' => $request->terms,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0) - ($item['discount_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);
        $purchaseOrder->load('items', 'supplier', 'currency', 'branch', 'project');

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);
        $companyId = Auth::user()->company_id;

        $purchaseOrder->load('items');
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();

        return view('purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'items', 'services', 'currencies', 'branches', 'projects'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                $taxAmount += $item['tax_amount'] ?? 0;
            }

            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'delivery_date' => $request->delivery_date,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'project_id' => $request->project_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total' => $subtotal + $taxAmount - ($request->discount_amount ?? 0),
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            $purchaseOrder->items()->delete();

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0) - ($item['discount_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update purchase order: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order deleted successfully.');
    }

    public function convertToInvoice(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);
        $companyId = Auth::user()->company_id;

        if ($purchaseOrder->status === 'INVOICED') {
            return back()->with('error', 'Purchase Order has already been invoiced.');
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = 'PINV-' . str_pad(
                PurchaseInvoice::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $invoice = PurchaseInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => $invoiceNumber,
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'currency_id' => $purchaseOrder->currency_id,
                'exchange_rate' => $purchaseOrder->exchange_rate,
                'branch_id' => $purchaseOrder->branch_id,
                'project_id' => $purchaseOrder->project_id,
                'subtotal' => $purchaseOrder->subtotal,
                'tax_amount' => $purchaseOrder->tax_amount,
                'discount_amount' => $purchaseOrder->discount_amount,
                'total' => $purchaseOrder->total,
                'paid_amount' => 0,
                'notes' => $purchaseOrder->notes,
                'terms' => $purchaseOrder->terms,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($purchaseOrder->items as $item) {
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id' => $item->item_id,
                    'service_id' => $item->service_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate_id' => $item->tax_rate_id,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'line_total' => $item->line_total,
                ]);
            }

            $purchaseOrder->update(['status' => 'INVOICED']);

            DB::commit();
            return redirect()->route('purchase-invoices.show', $invoice)->with('success', 'Purchase Order converted to Invoice successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to convert to invoice: ' . $e->getMessage());
        }
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorizeCompany($purchaseOrder);
        $purchaseOrder->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Purchase Order approved.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
