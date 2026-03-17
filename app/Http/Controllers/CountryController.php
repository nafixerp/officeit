<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $countries = Country::with('currency')
            ->orderBy('name')
            ->paginate(20);

        return view('countries.index', compact('countries'));
    }

    public function create()
    {
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('countries.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|size:2|unique:countries,iso_code',
            'iso3_code' => 'nullable|string|size:3',
            'phone_code' => 'nullable|string|max:10',
            'default_currency_id' => 'nullable|exists:currencies,id',
            'tax_type' => 'nullable|string|max:50',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_registration_format' => 'nullable|string|max:100',
            'invoice_number_format' => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:50',
            'financial_year_format' => 'nullable|string|max:50',
            'address_style' => 'nullable|string|max:50',
            'accounting_rules_profile' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        Country::create($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country created successfully.');
    }

    public function show(Country $country)
    {
        $country->load('currency', 'taxProfiles');

        return view('countries.show', compact('country'));
    }

    public function edit(Country $country)
    {
        $currencies = Currency::where('is_active', true)->orderBy('name')->get();

        return view('countries.edit', compact('country', 'currencies'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'iso_code' => 'required|string|size:2|unique:countries,iso_code,' . $country->id,
            'iso3_code' => 'nullable|string|size:3',
            'phone_code' => 'nullable|string|max:10',
            'default_currency_id' => 'nullable|exists:currencies,id',
            'tax_type' => 'nullable|string|max:50',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_registration_format' => 'nullable|string|max:100',
            'invoice_number_format' => 'nullable|string|max:100',
            'date_format' => 'nullable|string|max:50',
            'financial_year_format' => 'nullable|string|max:50',
            'address_style' => 'nullable|string|max:50',
            'accounting_rules_profile' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $country->update($validated);

        return redirect()->route('countries.index')
            ->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        $country->delete();

        return redirect()->route('countries.index')
            ->with('success', 'Country deleted successfully.');
    }
}
