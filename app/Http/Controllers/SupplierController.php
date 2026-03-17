<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Country;
use App\Models\Currency;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Supplier::where('company_id', $companyId)
            ->with(['country', 'currency']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers', 'countries'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('suppliers.create', compact('countries', 'currencies'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:suppliers,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_id' => 'nullable|exists:currencies,id',
            'payment_terms' => 'nullable|integer|min:0',
            'opening_balance' => 'nullable|numeric',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_swift_code' => 'nullable|string|max:50',
            'bank_iban' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $this->authorizeCompany($supplier);
        $supplier->load(['country', 'currency']);

        $outstandingBalance = PurchaseInvoice::where('supplier_id', $supplier->id)
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
            ->sum('balance_amount');

        return view('suppliers.show', compact('supplier', 'outstandingBalance'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizeCompany($supplier);
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('suppliers.edit', compact('supplier', 'countries', 'currencies'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizeCompany($supplier);
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_id' => 'nullable|exists:currencies,id',
            'payment_terms' => 'nullable|integer|min:0',
            'opening_balance' => 'nullable|numeric',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_swift_code' => 'nullable|string|max:50',
            'bank_iban' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorizeCompany($supplier);
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    public function ledger(Supplier $supplier, Request $request)
    {
        $this->authorizeCompany($supplier);
        $companyId = auth()->user()->company_id;

        $query = PurchaseInvoice::where('supplier_id', $supplier->id)
            ->where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->input('to_date'));
        }

        $transactions = $query->orderBy('date')->orderBy('id')->paginate(50)->withQueryString();

        return view('suppliers.ledger', compact('supplier', 'transactions'));
    }

    public function statement(Supplier $supplier, Request $request)
    {
        $this->authorizeCompany($supplier);
        $companyId = auth()->user()->company_id;

        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $openingBalance = $supplier->opening_balance +
            PurchaseInvoice::where('supplier_id', $supplier->id)
                ->where('company_id', $companyId)
                ->where('status', '!=', 'CANCELLED')
                ->whereDate('date', '<', $fromDate)
                ->sum('balance_amount');

        $transactions = PurchaseInvoice::where('supplier_id', $supplier->id)
            ->where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED')
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $closingBalance = $openingBalance + $transactions->sum('balance_amount');

        return view('suppliers.statement', compact(
            'supplier',
            'transactions',
            'openingBalance',
            'closingBalance',
            'fromDate',
            'toDate'
        ));
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
