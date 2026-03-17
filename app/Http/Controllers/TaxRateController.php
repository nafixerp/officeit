<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use App\Models\TaxProfile;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $taxRates = TaxRate::whereHas('taxProfile', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with('taxProfile')
            ->orderBy('name')
            ->paginate(20);

        return view('tax-rates.index', compact('taxRates'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $taxProfiles = TaxProfile::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tax-rates.create', compact('taxProfiles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tax_profile_id' => 'required|exists:tax_profiles,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:STANDARD,REDUCED,ZERO_RATED,EXEMPT,REVERSE_CHARGE',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Verify the tax profile belongs to the user's company
        $taxProfile = TaxProfile::findOrFail($validated['tax_profile_id']);
        if ($taxProfile->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        TaxRate::create($validated);

        return redirect()->route('tax-rates.index')
            ->with('success', 'Tax rate created successfully.');
    }

    public function show(TaxRate $taxRate)
    {
        $this->authorizeCompany($taxRate);
        $taxRate->load('taxProfile');

        return view('tax-rates.show', compact('taxRate'));
    }

    public function edit(TaxRate $taxRate)
    {
        $this->authorizeCompany($taxRate);
        $companyId = auth()->user()->company_id;

        $taxProfiles = TaxProfile::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tax-rates.edit', compact('taxRate', 'taxProfiles'));
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $this->authorizeCompany($taxRate);

        $validated = $request->validate([
            'tax_profile_id' => 'required|exists:tax_profiles,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:STANDARD,REDUCED,ZERO_RATED,EXEMPT,REVERSE_CHARGE',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Verify the tax profile belongs to the user's company
        $taxProfile = TaxProfile::findOrFail($validated['tax_profile_id']);
        if ($taxProfile->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        $taxRate->update($validated);

        return redirect()->route('tax-rates.index')
            ->with('success', 'Tax rate updated successfully.');
    }

    public function destroy(TaxRate $taxRate)
    {
        $this->authorizeCompany($taxRate);
        $taxRate->delete();

        return redirect()->route('tax-rates.index')
            ->with('success', 'Tax rate deleted successfully.');
    }

    private function authorizeCompany($taxRate)
    {
        $taxRate->load('taxProfile');
        if ($taxRate->taxProfile->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
