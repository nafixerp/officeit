<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $branches = Branch::where('company_id', $companyId)
            ->with('country')
            ->orderBy('name')
            ->paginate(20);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('branches.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'country_id' => 'nullable|exists:countries,id',
            'address' => 'nullable|string|max:500',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $this->authorizeCompany($branch);
        $branch->load('country');

        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $this->authorizeCompany($branch);
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('branches.edit', compact('branch', 'countries'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeCompany($branch);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'country_id' => 'nullable|exists:countries,id',
            'address' => 'nullable|string|max:500',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $this->authorizeCompany($branch);
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
