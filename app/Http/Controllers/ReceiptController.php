<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\ReceiptAllocation;
use App\Models\Customer;
use App\Models\SalesInvoice;
use App\Models\Currency;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $receipts = Receipt::where('company_id', $companyId)
            ->when($request->receipt_mode, fn($q, $mode) => $q->where('receipt_mode', $mode))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('receipt_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('receipt_date', '<=', $dateTo))
            ->with('customer')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('receipts.index', compact('receipts', 'customers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $bankAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->where('sub_type', 'BANK')
            ->where('is_active', true)
            ->get();
        $cashAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->where('sub_type', 'CASH')
            ->where('is_active', true)
            ->get();
        $currencies = Currency::where('is_active', true)->get();
        $unpaidInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID'])
            ->whereColumn('paid_amount', '<', 'total')
            ->with('customer')
            ->get();

        return view('receipts.create', compact('customers', 'bankAccounts', 'cashAccounts', 'currencies', 'unpaidInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'receipt_date' => 'required|date',
            'receipt_mode' => 'required|in:CASH,BANK,CHEQUE,TRANSFER',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
            'allocations' => 'nullable|array',
            'allocations.*.sales_invoice_id' => 'required|exists:sales_invoices,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $companyId = Auth::user()->company_id;

        $receiptNumber = 'RCT-' . str_pad(
            Receipt::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $receipt = Receipt::create([
                'company_id' => $companyId,
                'receipt_number' => $receiptNumber,
                'customer_id' => $request->customer_id,
                'receipt_date' => $request->receipt_date,
                'receipt_mode' => $request->receipt_mode,
                'account_id' => $request->account_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'POSTED',
                'created_by' => Auth::id(),
            ]);

            // Process allocations
            if ($request->allocations) {
                foreach ($request->allocations as $allocation) {
                    ReceiptAllocation::create([
                        'receipt_id' => $receipt->id,
                        'sales_invoice_id' => $allocation['sales_invoice_id'],
                        'amount' => $allocation['amount'],
                    ]);

                    // Update invoice paid amount and status
                    $invoice = SalesInvoice::find($allocation['sales_invoice_id']);
                    $invoice->paid_amount += $allocation['amount'];

                    if ($invoice->paid_amount >= $invoice->total) {
                        $invoice->status = 'PAID';
                    } else {
                        $invoice->status = 'PARTIALLY_PAID';
                    }

                    $invoice->save();
                }
            }

            $this->createReceiptJournalEntry($receipt);

            DB::commit();
            return redirect()->route('receipts.show', $receipt)->with('success', 'Receipt created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create receipt: ' . $e->getMessage());
        }
    }

    public function show(Receipt $receipt)
    {
        $this->authorizeCompany($receipt);
        $receipt->load('customer', 'allocations.salesInvoice', 'account');

        return view('receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt)
    {
        $this->authorizeCompany($receipt);
        $companyId = Auth::user()->company_id;

        $receipt->load('allocations');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $bankAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->where('sub_type', 'BANK')
            ->where('is_active', true)
            ->get();
        $cashAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('type', 'ASSET')
            ->where('sub_type', 'CASH')
            ->where('is_active', true)
            ->get();
        $currencies = Currency::where('is_active', true)->get();
        $unpaidInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID'])
            ->whereColumn('paid_amount', '<', 'total')
            ->with('customer')
            ->get();

        return view('receipts.edit', compact('receipt', 'customers', 'bankAccounts', 'cashAccounts', 'currencies', 'unpaidInvoices'));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $this->authorizeCompany($receipt);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'receipt_date' => 'required|date',
            'receipt_mode' => 'required|in:CASH,BANK,CHEQUE,TRANSFER',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Reverse previous allocations
            foreach ($receipt->allocations as $allocation) {
                $invoice = SalesInvoice::find($allocation->sales_invoice_id);
                if ($invoice) {
                    $invoice->paid_amount -= $allocation->amount;
                    if ($invoice->paid_amount <= 0) {
                        $invoice->status = 'APPROVED';
                        $invoice->paid_amount = 0;
                    } else {
                        $invoice->status = 'PARTIALLY_PAID';
                    }
                    $invoice->save();
                }
            }
            $receipt->allocations()->delete();

            $receipt->update([
                'customer_id' => $request->customer_id,
                'receipt_date' => $request->receipt_date,
                'receipt_mode' => $request->receipt_mode,
                'account_id' => $request->account_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'notes' => $request->notes,
            ]);

            // Process new allocations
            if ($request->allocations) {
                foreach ($request->allocations as $allocation) {
                    ReceiptAllocation::create([
                        'receipt_id' => $receipt->id,
                        'sales_invoice_id' => $allocation['sales_invoice_id'],
                        'amount' => $allocation['amount'],
                    ]);

                    $invoice = SalesInvoice::find($allocation['sales_invoice_id']);
                    $invoice->paid_amount += $allocation['amount'];

                    if ($invoice->paid_amount >= $invoice->total) {
                        $invoice->status = 'PAID';
                    } else {
                        $invoice->status = 'PARTIALLY_PAID';
                    }
                    $invoice->save();
                }
            }

            DB::commit();
            return redirect()->route('receipts.show', $receipt)->with('success', 'Receipt updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update receipt: ' . $e->getMessage());
        }
    }

    public function destroy(Receipt $receipt)
    {
        $this->authorizeCompany($receipt);

        DB::beginTransaction();
        try {
            // Reverse allocations
            foreach ($receipt->allocations as $allocation) {
                $invoice = SalesInvoice::find($allocation->sales_invoice_id);
                if ($invoice) {
                    $invoice->paid_amount -= $allocation->amount;
                    if ($invoice->paid_amount <= 0) {
                        $invoice->status = 'APPROVED';
                        $invoice->paid_amount = 0;
                    } else {
                        $invoice->status = 'PARTIALLY_PAID';
                    }
                    $invoice->save();
                }
            }

            $receipt->allocations()->delete();
            $receipt->delete();

            DB::commit();
            return redirect()->route('receipts.index')->with('success', 'Receipt deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete receipt: ' . $e->getMessage());
        }
    }

    private function createReceiptJournalEntry(Receipt $receipt)
    {
        $companyId = $receipt->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $receipt->receipt_date,
            'reference' => $receipt->receipt_number,
            'description' => 'Auto journal entry for Receipt ' . $receipt->receipt_number,
            'source_type' => 'Receipt',
            'source_id' => $receipt->id,
            'is_auto_generated' => true,
            'currency_id' => $receipt->currency_id,
            'exchange_rate' => $receipt->exchange_rate,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Cash/Bank Account
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receipt->account_id,
            'description' => 'Cash/Bank receipt - ' . $receipt->receipt_number,
            'debit_amount' => $receipt->amount,
            'credit_amount' => 0,
        ]);

        // Cr: Customer Receivables
        $customer = Customer::find($receipt->customer_id);
        $receivableAccountId = $customer->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('name', 'like', '%Accounts Receivable%')->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receivableAccountId,
            'description' => 'Customer receivable - ' . $receipt->receipt_number,
            'debit_amount' => 0,
            'credit_amount' => $receipt->amount,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
