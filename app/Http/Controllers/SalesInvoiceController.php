<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\Customer;
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

class SalesInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $salesInvoices = SalesInvoice::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('invoice_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('invoice_date', '<=', $dateTo))
            ->when($request->invoice_type, fn($q, $type) => $q->where('invoice_type', $type))
            ->with('customer')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('sales-invoices.index', compact('salesInvoices', 'customers'));
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
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $salesOrders = SalesOrder::where('company_id', $companyId)->where('status', 'APPROVED')->get();

        return view('sales-invoices.create', compact(
            'customers', 'items', 'services', 'currencies', 'branches',
            'projects', 'costCenters', 'taxRates', 'salesOrders'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $invoiceNumber = 'INV-' . str_pad(
            SalesInvoice::where('company_id', $companyId)->count() + 1,
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

            $invoice = SalesInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => $invoiceNumber,
                'sales_order_id' => $request->sales_order_id,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'invoice_type' => $request->invoice_type ?? 'STANDARD',
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

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
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

            $this->createSalesJournalEntry($invoice);

            DB::commit();
            return redirect()->route('sales-invoices.show', $invoice)->with('success', 'Sales Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create sales invoice: ' . $e->getMessage());
        }
    }

    public function show(SalesInvoice $salesInvoice)
    {
        $this->authorizeCompany($salesInvoice);
        $salesInvoice->load('items', 'customer', 'currency', 'branch', 'project', 'receipts');

        return view('sales-invoices.show', compact('salesInvoice'));
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        $this->authorizeCompany($salesInvoice);
        $companyId = Auth::user()->company_id;

        $salesInvoice->load('items');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $projects = Project::where('company_id', $companyId)->where('is_active', true)->get();
        $costCenters = CostCenter::where('company_id', $companyId)->where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $salesOrders = SalesOrder::where('company_id', $companyId)->where('status', 'APPROVED')->get();

        return view('sales-invoices.edit', compact(
            'salesInvoice', 'customers', 'items', 'services', 'currencies', 'branches',
            'projects', 'costCenters', 'taxRates', 'salesOrders'
        ));
    }

    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        $this->authorizeCompany($salesInvoice);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
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

            $salesInvoice->update([
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'invoice_type' => $request->invoice_type ?? 'STANDARD',
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

            $salesInvoice->items()->delete();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;

                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
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
            return redirect()->route('sales-invoices.show', $salesInvoice)->with('success', 'Sales Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update sales invoice: ' . $e->getMessage());
        }
    }

    public function destroy(SalesInvoice $salesInvoice)
    {
        $this->authorizeCompany($salesInvoice);

        $salesInvoice->items()->delete();
        $salesInvoice->delete();

        return redirect()->route('sales-invoices.index')->with('success', 'Sales Invoice deleted successfully.');
    }

    public function print(SalesInvoice $salesInvoice)
    {
        $this->authorizeCompany($salesInvoice);
        $salesInvoice->load('items', 'customer', 'currency', 'branch');

        return view('sales-invoices.print', compact('salesInvoice'));
    }

    private function createSalesJournalEntry(SalesInvoice $invoice)
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
            'description' => 'Auto journal entry for Sales Invoice ' . $invoice->invoice_number,
            'source_type' => 'SalesInvoice',
            'source_id' => $invoice->id,
            'is_auto_generated' => true,
            'currency_id' => $invoice->currency_id,
            'exchange_rate' => $invoice->exchange_rate,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Customer Receivables
        $customer = Customer::find($invoice->customer_id);
        $receivableAccountId = $customer->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('code', 'like', '%receivable%')->orWhere('name', 'like', '%Accounts Receivable%')->where('company_id', $companyId)->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receivableAccountId,
            'description' => 'Customer receivable - ' . $invoice->invoice_number,
            'debit_amount' => $invoice->total,
            'credit_amount' => 0,
            'cost_center_id' => $invoice->cost_center_id,
            'project_id' => $invoice->project_id,
        ]);

        // Cr: Sales Income
        $salesAccountId = ChartOfAccount::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '%sales%')->orWhere('name', 'like', '%Sales Income%');
            })
            ->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccountId,
            'description' => 'Sales income - ' . $invoice->invoice_number,
            'debit_amount' => 0,
            'credit_amount' => $invoice->subtotal,
            'cost_center_id' => $invoice->cost_center_id,
            'project_id' => $invoice->project_id,
        ]);

        // Cr: Output Tax (if applicable)
        if ($invoice->tax_amount > 0) {
            $taxAccountId = ChartOfAccount::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('code', 'like', '%output_tax%')->orWhere('name', 'like', '%Output Tax%');
                })
                ->value('id');

            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccountId,
                'description' => 'Output tax - ' . $invoice->invoice_number,
                'debit_amount' => 0,
                'credit_amount' => $invoice->tax_amount,
                'cost_center_id' => $invoice->cost_center_id,
                'project_id' => $invoice->project_id,
            ]);
        }
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
