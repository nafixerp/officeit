<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $purchaseReturns = PurchaseReturn::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->with('supplier', 'purchaseInvoice')
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        return view('purchase-returns.index', compact('purchaseReturns', 'suppliers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('purchase-returns.create', compact('suppliers', 'purchaseInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $returnNumber = 'PRN-' . str_pad(
            PurchaseReturn::where('company_id', $companyId)->count() + 1,
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

            $purchaseReturn = PurchaseReturn::create([
                'company_id' => $companyId,
                'return_number' => $returnNumber,
                'supplier_id' => $request->supplier_id,
                'purchase_invoice_id' => $request->purchase_invoice_id,
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
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase-returns.show', $purchaseReturn)->with('success', 'Purchase Return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create purchase return: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeCompany($purchaseReturn);
        $purchaseReturn->load('items', 'supplier', 'purchaseInvoice');

        return view('purchase-returns.show', compact('purchaseReturn'));
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeCompany($purchaseReturn);
        $companyId = Auth::user()->company_id;

        $purchaseReturn->load('items');
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('purchase-returns.edit', compact('purchaseReturn', 'suppliers', 'purchaseInvoices'));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $this->authorizeCompany($purchaseReturn);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_invoice_id' => 'required|exists:purchase_invoices,id',
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

            $purchaseReturn->update([
                'supplier_id' => $request->supplier_id,
                'purchase_invoice_id' => $request->purchase_invoice_id,
                'return_date' => $request->return_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $purchaseReturn->items()->delete();

            foreach ($request->items as $item) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('purchase-returns.show', $purchaseReturn)->with('success', 'Purchase Return updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update purchase return: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeCompany($purchaseReturn);

        $purchaseReturn->items()->delete();
        $purchaseReturn->delete();

        return redirect()->route('purchase-returns.index')->with('success', 'Purchase Return deleted successfully.');
    }

    public function approve(PurchaseReturn $purchaseReturn)
    {
        $this->authorizeCompany($purchaseReturn);

        DB::beginTransaction();
        try {
            $purchaseReturn->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

            $this->createReverseJournalEntry($purchaseReturn);

            DB::commit();
            return back()->with('success', 'Purchase Return approved and journal entry reversed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve purchase return: ' . $e->getMessage());
        }
    }

    private function createReverseJournalEntry(PurchaseReturn $purchaseReturn)
    {
        $companyId = $purchaseReturn->company_id;

        $entryNumber = 'JE-' . str_pad(
            JournalEntry::where('company_id', $companyId)->count() + 1,
            6, '0', STR_PAD_LEFT
        );

        $journalEntry = JournalEntry::create([
            'company_id' => $companyId,
            'entry_number' => $entryNumber,
            'entry_date' => $purchaseReturn->return_date,
            'reference' => $purchaseReturn->return_number,
            'description' => 'Reverse journal entry for Purchase Return ' . $purchaseReturn->return_number,
            'source_type' => 'PurchaseReturn',
            'source_id' => $purchaseReturn->id,
            'is_auto_generated' => true,
            'status' => 'POSTED',
            'created_by' => Auth::id(),
        ]);

        // Dr: Supplier/Accounts Payable (reverse the original credit)
        $supplier = Supplier::find($purchaseReturn->supplier_id);
        $payableAccountId = $supplier->account_id
            ?? ChartOfAccount::where('company_id', $companyId)->where('name', 'like', '%Accounts Payable%')->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccountId,
            'description' => 'Supplier payable reversal - ' . $purchaseReturn->return_number,
            'debit_amount' => $purchaseReturn->total,
            'credit_amount' => 0,
        ]);

        // Cr: Purchase/Expense (reverse the original debit)
        $purchaseAccountId = ChartOfAccount::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('code', 'like', '%purchase%')->orWhere('name', 'like', '%Purchases%');
            })
            ->value('id');

        JournalLine::create([
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $purchaseAccountId,
            'description' => 'Purchase expense reversal - ' . $purchaseReturn->return_number,
            'debit_amount' => 0,
            'credit_amount' => $purchaseReturn->subtotal,
        ]);

        // Cr: Input Tax (if applicable, reverse the original debit)
        if ($purchaseReturn->tax_amount > 0) {
            $taxAccountId = ChartOfAccount::where('company_id', $companyId)
                ->where(function ($q) {
                    $q->where('code', 'like', '%input_tax%')->orWhere('name', 'like', '%Input Tax%');
                })
                ->value('id');

            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccountId,
                'description' => 'Input tax reversal - ' . $purchaseReturn->return_number,
                'debit_amount' => 0,
                'credit_amount' => $purchaseReturn->tax_amount,
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
