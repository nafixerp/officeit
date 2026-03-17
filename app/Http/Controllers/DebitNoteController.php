<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\DebitNoteItem;
use App\Models\Supplier;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebitNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $debitNotes = DebitNote::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->supplier_id, fn($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->with('supplier', 'purchaseInvoice')
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();

        return view('debit-notes.index', compact('debitNotes', 'suppliers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('debit-notes.create', compact('suppliers', 'purchaseInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'debit_note_date' => 'required|date',
            'purchase_invoice_id' => 'nullable|exists:purchase_invoices,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $debitNoteNumber = 'DN-' . str_pad(
            DebitNote::where('company_id', $companyId)->count() + 1,
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

            $debitNote = DebitNote::create([
                'company_id' => $companyId,
                'debit_note_number' => $debitNoteNumber,
                'supplier_id' => $request->supplier_id,
                'purchase_invoice_id' => $request->purchase_invoice_id,
                'debit_note_date' => $request->debit_note_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                DebitNoteItem::create([
                    'debit_note_id' => $debitNote->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('debit-notes.show', $debitNote)->with('success', 'Debit Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create debit note: ' . $e->getMessage());
        }
    }

    public function show(DebitNote $debitNote)
    {
        $this->authorizeCompany($debitNote);
        $debitNote->load('items', 'supplier', 'purchaseInvoice');

        return view('debit-notes.show', compact('debitNote'));
    }

    public function edit(DebitNote $debitNote)
    {
        $this->authorizeCompany($debitNote);
        $companyId = Auth::user()->company_id;

        $debitNote->load('items');
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->get();
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('debit-notes.edit', compact('debitNote', 'suppliers', 'purchaseInvoices'));
    }

    public function update(Request $request, DebitNote $debitNote)
    {
        $this->authorizeCompany($debitNote);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'debit_note_date' => 'required|date',
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

            $debitNote->update([
                'supplier_id' => $request->supplier_id,
                'purchase_invoice_id' => $request->purchase_invoice_id,
                'debit_note_date' => $request->debit_note_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $debitNote->items()->delete();

            foreach ($request->items as $item) {
                DebitNoteItem::create([
                    'debit_note_id' => $debitNote->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('debit-notes.show', $debitNote)->with('success', 'Debit Note updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update debit note: ' . $e->getMessage());
        }
    }

    public function destroy(DebitNote $debitNote)
    {
        $this->authorizeCompany($debitNote);

        $debitNote->items()->delete();
        $debitNote->delete();

        return redirect()->route('debit-notes.index')->with('success', 'Debit Note deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
