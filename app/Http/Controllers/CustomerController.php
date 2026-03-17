<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Country;
use App\Models\Currency;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Customer::where('company_id', $companyId)
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

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->input('customer_type'));
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->input('country_id'));
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('customers.index', compact('customers', 'countries'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('customers.create', compact('countries', 'currencies'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:customers,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:LOCAL,EXPORT,PROJECT,RETAIL,WHOLESALE,SERVICE',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_id' => 'nullable|exists:currencies,id',
            'credit_days' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'salesperson' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $this->authorizeCompany($customer);
        $customer->load(['country', 'currency']);

        // Calculate outstanding balance
        $outstandingBalance = SalesInvoice::where('customer_id', $customer->id)
            ->where('company_id', auth()->user()->company_id)
            ->whereIn('status', ['APPROVED', 'PARTIALLY_PAID'])
            ->sum('balance_amount');

        return view('customers.show', compact('customer', 'outstandingBalance'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeCompany($customer);
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('customers.edit', compact('customer', 'countries', 'currencies'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeCompany($customer);
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:customers,code,' . $customer->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'customer_type' => 'required|in:LOCAL,EXPORT,PROJECT,RETAIL,WHOLESALE,SERVICE',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'tax_number' => 'nullable|string|max:100',
            'currency_id' => 'nullable|exists:currencies,id',
            'credit_days' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'salesperson' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'contact_person' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeCompany($customer);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function ledger(Customer $customer, Request $request)
    {
        $this->authorizeCompany($customer);
        $companyId = auth()->user()->company_id;

        $query = SalesInvoice::where('customer_id', $customer->id)
            ->where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->input('to_date'));
        }

        $transactions = $query->orderBy('date')->orderBy('id')->paginate(50)->withQueryString();

        return view('customers.ledger', compact('customer', 'transactions'));
    }

    public function statement(Customer $customer, Request $request)
    {
        $this->authorizeCompany($customer);
        $companyId = auth()->user()->company_id;

        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        // Opening balance (all invoices before from_date)
        $openingBalance = $customer->opening_balance +
            SalesInvoice::where('customer_id', $customer->id)
                ->where('company_id', $companyId)
                ->where('status', '!=', 'CANCELLED')
                ->whereDate('date', '<', $fromDate)
                ->sum('balance_amount');

        // Transactions in period
        $transactions = SalesInvoice::where('customer_id', $customer->id)
            ->where('company_id', $companyId)
            ->where('status', '!=', 'CANCELLED')
            ->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Closing balance
        $closingBalance = $openingBalance + $transactions->sum('balance_amount');

        return view('customers.statement', compact(
            'customer',
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
