<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Service;
use App\Models\Currency;
use App\Models\TaxRate;
use App\Models\Branch;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $quotations = Quotation::where('company_id', $companyId)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->customer_id, fn($q, $customerId) => $q->where('customer_id', $customerId))
            ->when($request->date_from, fn($q, $dateFrom) => $q->where('quotation_date', '>=', $dateFrom))
            ->when($request->date_to, fn($q, $dateTo) => $q->where('quotation_date', '<=', $dateTo))
            ->with('customer')
            ->latest()
            ->paginate(20);

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();

        return view('quotations.index', compact('quotations', 'customers'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('quotations.create', compact('customers', 'items', 'services', 'currencies', 'taxRates', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'currency_id' => 'required|exists:currencies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        $quotationNumber = 'QTN-' . str_pad(
            Quotation::where('company_id', $companyId)->count() + 1,
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

            $quotation = Quotation::create([
                'company_id' => $companyId,
                'quotation_number' => $quotationNumber,
                'customer_id' => $request->customer_id,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
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
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
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
            return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create quotation: ' . $e->getMessage());
        }
    }

    public function show(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $quotation->load('items', 'customer', 'currency', 'branch');

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $companyId = Auth::user()->company_id;

        $quotation->load('items');
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->get();
        $items = Item::where('company_id', $companyId)->where('is_active', true)->get();
        $services = Service::where('company_id', $companyId)->where('is_active', true)->get();
        $currencies = Currency::where('is_active', true)->get();
        $taxRates = TaxRate::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('quotations.edit', compact('quotation', 'customers', 'items', 'services', 'currencies', 'taxRates', 'branches'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeCompany($quotation);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
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

            $quotation->update([
                'customer_id' => $request->customer_id,
                'quotation_date' => $request->quotation_date,
                'valid_until' => $request->valid_until,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate ?? 1,
                'branch_id' => $request->branch_id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total' => $subtotal + $taxAmount - ($request->discount_amount ?? 0),
                'notes' => $request->notes,
                'terms' => $request->terms,
            ]);

            $quotation->items()->delete();

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = isset($item['tax_rate_id']) && $item['tax_rate_id']
                    ? $lineTotal * (TaxRate::find($item['tax_rate_id'])->rate / 100)
                    : 0;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
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
            return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update quotation: ' . $e->getMessage());
        }
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);

        $quotation->items()->delete();
        $quotation->delete();

        return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function convertToOrder(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $companyId = Auth::user()->company_id;

        if ($quotation->status === 'CONVERTED') {
            return back()->with('error', 'Quotation has already been converted.');
        }

        DB::beginTransaction();
        try {
            $orderNumber = 'SO-' . str_pad(
                SalesOrder::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );

            $salesOrder = SalesOrder::create([
                'company_id' => $companyId,
                'order_number' => $orderNumber,
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'order_date' => now(),
                'currency_id' => $quotation->currency_id,
                'exchange_rate' => $quotation->exchange_rate,
                'branch_id' => $quotation->branch_id,
                'subtotal' => $quotation->subtotal,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'total' => $quotation->total,
                'notes' => $quotation->notes,
                'terms' => $quotation->terms,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
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

            $quotation->update(['status' => 'CONVERTED']);

            DB::commit();
            return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Quotation converted to Sales Order successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to convert quotation: ' . $e->getMessage());
        }
    }

    public function pdf(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $quotation->load('items', 'customer', 'currency', 'branch');

        return view('quotations.pdf', compact('quotation'));
    }

    public function send(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);

        // TODO: Implement email sending logic
        // Mail::to($quotation->customer->email)->send(new QuotationMail($quotation));

        $quotation->update(['status' => 'SENT']);

        return back()->with('success', 'Quotation sent successfully.');
    }

    public function approve(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $quotation->update(['status' => 'APPROVED', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Quotation approved.');
    }

    public function reject(Quotation $quotation)
    {
        $this->authorizeCompany($quotation);
        $quotation->update(['status' => 'REJECTED']);

        return back()->with('success', 'Quotation rejected.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
