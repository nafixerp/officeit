<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Customer;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreditNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $creditNotes = CreditNote::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->with('customer', 'salesInvoice')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('credit-notes.index', compact('creditNotes', 'customers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $salesInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('credit-notes.create', compact('customers', 'salesInvoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credit_note_date' => 'required|date',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $creditNoteNumber = 'CN-' . str_pad(
            CreditNote::where('company_id', $companyId)->count() + 1,
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

            $creditNote = CreditNote::create([
                'company_id' => $companyId,
                'credit_note_number' => $creditNoteNumber,
                'customer_id' => $request->customer_id,
                'sales_invoice_id' => $request->sales_invoice_id,
                'credit_note_date' => $request->credit_note_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('credit-notes.show', $creditNote)->with('success', 'Credit Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create credit note: ' . $e->getMessage());
        }
    }

    public function show(CreditNote $creditNote)
    {
        $this->authorizeCompany($creditNote);
        $creditNote->load('items', 'customer', 'salesInvoice');

        return view('credit-notes.show', compact('creditNote'));
    }

    public function edit(CreditNote $creditNote)
    {
        $this->authorizeCompany($creditNote);
        $companyId = Auth::user()->company_id;

        $creditNote->load('items');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $salesInvoices = SalesInvoice::where('company_id', $companyId)
            ->whereIn('status', ['APPROVED', 'POSTED', 'PARTIALLY_PAID', 'PAID'])
            ->get();

        return view('credit-notes.edit', compact('creditNote', 'customers', 'salesInvoices'));
    }

    public function update(Request $request, CreditNote $creditNote)
    {
        $this->authorizeCompany($creditNote);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'credit_note_date' => 'required|date',
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

            $creditNote->update([
                'customer_id' => $request->customer_id,
                'sales_invoice_id' => $request->sales_invoice_id,
                'credit_note_date' => $request->credit_note_date,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            $creditNote->items()->delete();

            foreach ($request->items as $item) {
                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'item_id' => $item['item_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'line_total' => ($item['quantity'] * $item['unit_price']) + ($item['tax_amount'] ?? 0),
                ]);
            }

            DB::commit();
            return redirect()->route('credit-notes.show', $creditNote)->with('success', 'Credit Note updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update credit note: ' . $e->getMessage());
        }
    }

    public function destroy(CreditNote $creditNote)
    {
        $this->authorizeCompany($creditNote);

        $creditNote->items()->delete();
        $creditNote->delete();

        return redirect()->route('credit-notes.index')->with('success', 'Credit Note deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
