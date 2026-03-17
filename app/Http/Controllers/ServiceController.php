<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $query = Service::where('company_id', $companyId)
            ->with('taxRate');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $taxRates = TaxRate::whereHas('taxProfile', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('is_active', true);
        })->where('is_active', true)->get();

        return view('services.create', compact('taxRates'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:services,code,NULL,id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'billing_type' => 'required|in:FIXED,HOURLY,MONTHLY,PROJECT',
            'rate' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'description' => 'nullable|string|max:1000',
            'income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        $this->authorizeCompany($service);
        $service->load('taxRate');

        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        $this->authorizeCompany($service);
        $companyId = auth()->user()->company_id;

        $taxRates = TaxRate::whereHas('taxProfile', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->where('is_active', true);
        })->where('is_active', true)->get();

        return view('services.edit', compact('service', 'taxRates'));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorizeCompany($service);
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:services,code,' . $service->id . ',id,company_id,' . $companyId,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'billing_type' => 'required|in:FIXED,HOURLY,MONTHLY,PROJECT',
            'rate' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'description' => 'nullable|string|max:1000',
            'income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $this->authorizeCompany($service);
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
