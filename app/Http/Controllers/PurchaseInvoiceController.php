<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\Service;
use App\Models\Currency;
use App\Models\Branch;
use App\Models\Project;
use App\Models\CostCenter;
use App\Models\TaxRate;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('invoice_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('invoice_date', '<=', $dateTo))
            ->with('supplier')
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        return view('purchase-invoices.index', compact('purchaseInvoices', 'suppliers'));
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
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseOrders = PurchaseOrder::where('company_id', $companyId)->where('status', 'APPROVED')->get();

        return view('purchase-invoices.create', compact(
            'suppliers', 'items', 'services', 'currencies', 'branches',
            'projects', 'costCenters', 'taxRates', 'purchaseOrders'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $invoiceNumber = 'PINV-' . str_pad(
            PurchaseInvoice::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;
                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
            }

            $invoice = PurchaseInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => $invoiceNumber,
                'purchase_order_id' => $request->purchase_order_id,
                'supplier_id' => $request->supplier_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'project_id' => $request->project_id,
                'cost_center_id' => $request->cost_center_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total' => $subtotal + $taxAmount - ($request->discount_amount ?? 0),
                'paid_amount' => 0,
                'notes' => $request->notes,
                'terms' => $request->terms,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_id' => $item['item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'tax_amount' => $lineTax,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $lineTax - ($item['discount_amount'] ?? 0),
                ]);
            }

            $this->createPurchaseJournalEntry($invoice);

            DB::commit();
            return redirect()->route('purchase-invoices.show', $invoice)->with('success', 'Purchase Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create purchase invoice: ' . $e->getMessage());
        }
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorizeCompany($purchaseInvoice);
        $purchaseInvoice->load('items', 'supplier', 'currency', 'branch', 'project', 'payments');

        return view('purchase-invoices.show', compact('purchaseInvoice'));
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorizeCompany($purchaseInvoice);
        $companyId = Auth::user()->company_id;

        $purchaseInvoice->load('items');
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseOrders = PurchaseOrder::where('company_id', $companyId)->where('status', 'APPROVED')->get();

        return view('purchase-invoices.edit', compact(
            'purchaseInvoice', 'suppliers', 'items', 'services', 'currencies', 'branches',
            'projects', 'costCenters', 'taxRates', 'purchaseOrders'
        ));
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $this->authorizeCompany($purchaseInvoice);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
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
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;
                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
            }

            $purchaseInvoice->update([
                'supplier_id' => $request->supplier_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'project_id' => $request->project_id,
                'cost_center_id' => $request->cost_center_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total' => $subtotal + $taxAmount - ($request->discount_amount ?? 0),
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            $purchaseInvoice->items()->delete();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'item_id' => $item['item_id'] ?? null,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate_id' => $item['tax_rate_id'] ?? null,
                    'tax_amount' => $lineTax,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $lineTax - ($item['discount_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase-invoices.show', $purchaseInvoice)->with('success', 'Purchase Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update purchase invoice: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorizeCompany($purchaseInvoice);

        $purchaseInvoice->items()->delete();
        $purchaseInvoice->delete();

        return redirect()->route('purchase-invoices.index')->with('success', 'Purchase Invoice deleted successfully.');
    }

    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $this->authorizeCompany($purchaseInvoice);
        $purchaseInvoice->load('items', 'supplier', 'currency', 'branch');

        return view('purchase-invoices.print', compact('purchaseInvoice'));
    }

    private function createPurchaseJournalEntry(PurchaseInvoice $invoice)
    {
        $companyId = $invoice->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'description' => 'Auto journal entry for Purchase Invoice ' . $invoice->invoice_number,
            'source_type' => 'PurchaseInvoice',
            'source_id' => $invoice->id,
            'is_auto_generated' => true,
            'currency_id' => $invoice->currency_id,
            'exchange_rate' => $invoice->exchange_rate,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Purchase/Expense Account
        $purchaseAccountId = ChartOfAccount::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '%purchase%')->orWhere('name', 'like', '%Purchases%');
            })
            ->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $purchaseAccountId,
            'description' => 'Purchase expense - ' . $invoice->invoice_number,
            'debit_amount' => $invoice->subtotal,
            'credit_amount' => 0,
            'cost_center_id' => $invoice->cost_center_id,
            'project_id' => $invoice->project_id,
        ]);

        // Dr: Input Tax (if applicable)
        if ($invoice->tax_amount > 0) {
            $taxAccountId = ChartOfAccount::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('code', 'like', '%input_tax%')->orWhere('name', 'like', '%Input Tax%');
                })
                ->value('id');

            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccountId,
                'description' => 'Input tax - ' . $invoice->invoice_number,
                'debit_amount' => $invoice->tax_amount,
                'credit_amount' => 0,
                'cost_center_id' => $invoice->cost_center_id,
                'project_id' => $invoice->project_id,
            ]);
        }

        // Cr: Supplier/Accounts Payable
        $supplier = Supplier::find($invoice->supplier_id);
        $payableAccountId = $supplier->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('name', 'like', '%Accounts Payable%')->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccountId,
            'description' => 'Supplier payable - ' . $invoice->invoice_number,
            'debit_amount' => 0,
            'credit_amount' => $invoice->total,
            'cost_center_id' => $invoice->cost_center_id,
            'project_id' => $invoice->project_id,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
