<?php

namespace App\Http\Controllers;

use App\Models\TaxProfile;
use App\Models\Country;
use Illuminate\Http\Request;

class TaxProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $taxProfiles = TaxProfile::where('company_id', $companyId)
            ->with('country')
            ->orderBy('name')
            ->paginate(20);

        return view('tax-profiles.index', compact('taxProfiles'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('tax-profiles.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'tax_type' => 'required|in:VAT,GST,SALES_TAX,NONE',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        TaxProfile::create($validated);

        return redirect()->route('tax-profiles.index')
            ->with('success', 'Tax profile created successfully.');
    }

    public function show(TaxProfile $taxProfile)
    {
        $this->authorizeCompany($taxProfile);
        $taxProfile->load(['country', 'taxRates']);

        return view('tax-profiles.show', compact('taxProfile'));
    }

    public function edit(TaxProfile $taxProfile)
    {
        $this->authorizeCompany($taxProfile);
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('tax-profiles.edit', compact('taxProfile', 'countries'));
    }

    public function update(Request $request, TaxProfile $taxProfile)
    {
        $this->authorizeCompany($taxProfile);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'tax_type' => 'required|in:VAT,GST,SALES_TAX,NONE',
            'is_active' => 'boolean',
        ]);

        $taxProfile->update($validated);

        return redirect()->route('tax-profiles.index')
            ->with('success', 'Tax profile updated successfully.');
    }

    public function destroy(TaxProfile $taxProfile)
    {
        $this->authorizeCompany($taxProfile);
        $taxProfile->delete();

        return redirect()->route('tax-profiles.index')
            ->with('success', 'Tax profile deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
