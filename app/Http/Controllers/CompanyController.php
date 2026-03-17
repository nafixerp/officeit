<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companies = Company::with(['country', 'currency'])
            ->orderBy('name')
            ->paginate(20);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('companies.create', compact('countries', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'default_currency_id' => 'nullable|exists:currencies,id',
            'financial_year_start' => 'nullable|date',
            'timezone' => 'nullable|string|max:100',
            'invoice_prefix' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        $company->load(['country', 'currency', 'branches', 'departments']);

        return view('companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('companies.edit', compact('company', 'countries', 'currencies'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'default_currency_id' => 'nullable|exists:currencies,id',
            'financial_year_start' => 'nullable|date',
            'timezone' => 'nullable|string|max:100',
            'invoice_prefix' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
