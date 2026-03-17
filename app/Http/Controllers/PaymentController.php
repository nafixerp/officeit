<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Supplier;
use App\Models\PurchaseInvoice;
use App\Models\Currency;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $payments = Payment::where('company_id', $companyId)
            ->when($request->payment_mode, fn($q, $mode) => $q->where('payment_mode', $mode))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('payment_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('payment_date', '<=', $dateTo))
            ->with('supplier')
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        return view('payments.index', compact('payments', 'suppliers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
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
        $unpaidInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID'])
            ->whereColumn('paid_amount', '<', 'total')
            ->with('supplier')
            ->get();

        return view('payments.create', compact('suppliers', 'bankAccounts', 'cashAccounts', 'currencies', 'unpaidInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:CASH,BANK,CHEQUE,TRANSFER',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
            'allocations' => 'nullable|array',
            'allocations.*.purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $companyId = Auth::user()->company_id;

        $paymentNumber = 'PAY-' . str_pad(
            Payment::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'company_id' => $companyId,
                'payment_number' => $paymentNumber,
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'account_id' => $request->account_id,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            // Process allocations
            if ($request->allocations) {
                foreach ($request->allocations as $allocation) {
                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'purchase_invoice_id' => $allocation['purchase_invoice_id'],
                        'amount' => $allocation['amount'],
                    ]);

                    // Update invoice paid amount and status
                    $invoice = PurchaseInvoice::find($allocation['purchase_invoice_id']);
                    $invoice->paid_amount += $allocation['amount'];

                    if ($invoice->paid_amount >= $invoice->total) {
                        $invoice->status = 'PAID';
                    } else {
                        $invoice->status = 'PARTIALLY_PAID';
                    }

                    $invoice->save();
                }
            }

            $this->createPaymentJournalEntry($payment);

            DB::commit();
            return redirect()->route('payments.show', $payment)->with('success', 'Payment created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create payment: ' . $e->getMessage());
        }
    }

    public function show(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->load('supplier', 'allocations.purchaseInvoice', 'account');

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $companyId = Auth::user()->company_id;

        $payment->load('allocations');
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
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
        $unpaidInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID'])
            ->whereColumn('paid_amount', '<', 'total')
            ->with('supplier')
            ->get();

        return view('payments.edit', compact('payment', 'suppliers', 'bankAccounts', 'cashAccounts', 'currencies', 'unpaidInvoices'));
    }

    public function update(Request $request, Payment $payment)
    {
        $this->authorizeCompany($payment);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:CASH,BANK,CHEQUE,TRANSFER',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Reverse previous allocations
            foreach ($payment->allocations as $allocation) {
                $invoice = PurchaseInvoice::find($allocation->purchase_invoice_id);
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
            $payment->allocations()->delete();

            $payment->update([
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
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
                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'purchase_invoice_id' => $allocation['purchase_invoice_id'],
                        'amount' => $allocation['amount'],
                    ]);

                    $invoice = PurchaseInvoice::find($allocation['purchase_invoice_id']);
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
            return redirect()->route('payments.show', $payment)->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
    }

    public function destroy(Payment $payment)
    {
        $this->authorizeCompany($payment);

        DB::beginTransaction();
        try {
            // Reverse allocations
            foreach ($payment->allocations as $allocation) {
                $invoice = PurchaseInvoice::find($allocation->purchase_invoice_id);
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

            $payment->allocations()->delete();
            $payment->delete();

            DB::commit();
            return redirect()->route('payments.index')->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    public function approve(Payment $payment)
    {
        $this->authorizeCompany($payment);
        $payment->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Payment approved.');
    }

    private function createPaymentJournalEntry(Payment $payment)
    {
        $companyId = $payment->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $payment->payment_date,
            'reference' => $payment->payment_number,
            'description' => 'Auto journal entry for Payment ' . $payment->payment_number,
            'source_type' => 'Payment',
            'source_id' => $payment->id,
            'is_auto_generated' => true,
            'currency_id' => $payment->currency_id,
            'exchange_rate' => $payment->exchange_rate,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Supplier/Accounts Payable
        $supplier = Supplier::find($payment->supplier_id);
        $payableAccountId = $supplier->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('name', 'like', '%Accounts Payable%')->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccountId,
            'description' => 'Supplier payment - ' . $payment->payment_number,
            'debit_amount' => $payment->amount,
            'credit_amount' => 0,
        ]);

        // Cr: Cash/Bank Account
        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payment->account_id,
            'description' => 'Cash/Bank payment - ' . $payment->payment_number,
            'debit_amount' => 0,
            'credit_amount' => $payment->amount,
        ]);
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
