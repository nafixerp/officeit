<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Service;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $salesOrders = SalesOrder::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->with('customer')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('sales-orders.index', compact('salesOrders', 'customers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();

        return view('sales-orders.create', compact('customers', 'items', 'services', 'currencies', 'branches', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $orderNumber = 'SO-' . str_pad(
            SalesOrder::where('company_id', $companyId)->count() + 1,
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

            $salesOrder = SalesOrder::create([
                'company_id' => $companyId,
                'order_number' => $orderNumber,
                'customer_id' => $request->customer_id,
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
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
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
            return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Sales Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create sales order: ' . $e->getMessage());
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);
        $salesOrder->load('items', 'customer', 'currency', 'branch', 'project');

        return view('sales-orders.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);
        $companyId = Auth::user()->company_id;

        $salesOrder->load('items');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();

        return view('sales-orders.edit', compact('salesOrder', 'customers', 'items', 'services', 'currencies', 'branches', 'projects'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
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

            $salesOrder->update([
                'customer_id' => $request->customer_id,
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

            $salesOrder->items()->delete();

            foreach ($request->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
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
            return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Sales Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update sales order: ' . $e->getMessage());
        }
    }

    public function destroy(SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);

        $salesOrder->items()->delete();
        $salesOrder->delete();

        return redirect()->route('sales-orders.index')->with('success', 'Sales Order deleted successfully.');
    }

    public function convertToInvoice(SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);
        $companyId = Auth::user()->company_id;

        if ($salesOrder->status === 'INVOICED') {
            return back()->with('error', 'Sales Order has already been invoiced.');
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = 'INV-' . str_pad(
                SalesInvoice::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $invoice = SalesInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => $invoiceNumber,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'currency_id' => $salesOrder->currency_id,
                'exchange_rate' => $salesOrder->exchange_rate,
                'branch_id' => $salesOrder->branch_id,
                'project_id' => $salesOrder->project_id,
                'subtotal' => $salesOrder->subtotal,
                'tax_amount' => $salesOrder->tax_amount,
                'discount_amount' => $salesOrder->discount_amount,
                'total' => $salesOrder->total,
                'paid_amount' => 0,
                'notes' => $salesOrder->notes,
                'terms' => $salesOrder->terms,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($salesOrder->items as $item) {
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
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

            $salesOrder->update(['status' => 'INVOICED']);

            DB::commit();
            return redirect()->route('sales-invoices.show', $invoice)->with('success', 'Sales Order converted to Invoice successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to convert to invoice: ' . $e->getMessage());
        }
    }

    public function approve(SalesOrder $salesOrder)
    {
        $this->authorizeCompany($salesOrder);
        $salesOrder->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Sales Order approved.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
