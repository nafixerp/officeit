<?php

namespace App\Http\Controllers;

use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\SalesInvoice;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $salesReturns = SalesReturn::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->with('customer', 'salesInvoice')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('sales-returns.index', compact('salesReturns', 'customers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $salesInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('sales-returns.create', compact('customers', 'salesInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_invoice_id' => 'required|exists:sales_invoices,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $returnNumber = 'SRN-' . str_pad(
            SalesReturn::where('company_id', $companyId)->count() + 1,
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

            $salesReturn = SalesReturn::create([
                'company_id' => $companyId,
                'return_number' => $returnNumber,
                'customer_id' => $request->customer_id,
                'sales_invoice_id' => $request->sales_invoice_id,
                'return_date' => $request->return_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('sales-returns.show', $salesReturn)->with('success', 'Sales Return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create sales return: ' . $e->getMessage());
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $this->authorizeCompany($salesReturn);
        $salesReturn->load('items', 'customer', 'salesInvoice');

        return view('sales-returns.show', compact('salesReturn'));
    }

    public function edit(SalesReturn $salesReturn)
    {
        $this->authorizeCompany($salesReturn);
        $companyId = Auth::user()->company_id;

        $salesReturn->load('items');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $salesInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('sales-returns.edit', compact('salesReturn', 'customers', 'salesInvoices'));
    }

    public function update(Request $request, SalesReturn $salesReturn)
    {
        $this->authorizeCompany($salesReturn);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_invoice_id' => 'required|exists:sales_invoices,id',
            'return_date' => 'required|date',
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

            $salesReturn->update([
                'customer_id' => $request->customer_id,
                'sales_invoice_id' => $request->sales_invoice_id,
                'return_date' => $request->return_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $salesReturn->items()->delete();

            foreach ($request->items as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('sales-returns.show', $salesReturn)->with('success', 'Sales Return updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update sales return: ' . $e->getMessage());
        }
    }

    public function destroy(SalesReturn $salesReturn)
    {
        $this->authorizeCompany($salesReturn);

        $salesReturn->items()->delete();
        $salesReturn->delete();

        return redirect()->route('sales-returns.index')->with('success', 'Sales Return deleted successfully.');
    }

    public function approve(SalesReturn $salesReturn)
    {
        $this->authorizeCompany($salesReturn);

        DB::beginTransaction();
        try {
            $salesReturn->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

            $this->createReverseJournalEntry($salesReturn);

            DB::commit();
            return back()->with('success', 'Sales Return approved and journal entry reversed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve sales return: ' . $e->getMessage());
        }
    }

    private function createReverseJournalEntry(SalesReturn $salesReturn)
    {
        $companyId = $salesReturn->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $salesReturn->return_date,
            'reference' => $salesReturn->return_number,
            'description' => 'Reverse journal entry for Sales Return ' . $salesReturn->return_number,
            'source_type' => 'SalesReturn',
            'source_id' => $salesReturn->id,
            'is_auto_generated' => true,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Sales Income (reverse the original credit)
        $salesAccountId = ChartOfAccount::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '%sales%')->orWhere('name', 'like', '%Sales Income%');
            })
            ->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesAccountId,
            'description' => 'Sales return reversal - ' . $salesReturn->return_number,
            'debit_amount' => $salesReturn->subtotal,
            'credit_amount' => 0,
        ]);

        // Dr: Output Tax (if applicable, reverse the original credit)
        if ($salesReturn->tax_amount > 0) {
            $taxAccountId = ChartOfAccount::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('code', 'like', '%output_tax%')->orWhere('name', 'like', '%Output Tax%');
                })
                ->value('id');

            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccountId,
                'description' => 'Output tax reversal - ' . $salesReturn->return_number,
                'debit_amount' => $salesReturn->tax_amount,
                'credit_amount' => 0,
            ]);
        }

        // Cr: Customer Receivables (reverse the original debit)
        $customer = Customer::find($salesReturn->customer_id);
        $receivableAccountId = $customer->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('name', 'like', '%Accounts Receivable%')->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receivableAccountId,
            'description' => 'Customer receivable reversal - ' . $salesReturn->return_number,
            'debit_amount' => 0,
            'credit_amount' => $salesReturn->total,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
