<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $costCenters = CostCenter::where('company_id', $companyId)
            ->with(['branch', 'department'])
            ->orderBy('name')
            ->paginate(20);

        return view('cost-centers.index', compact('costCenters'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cost-centers.create', compact('branches', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        CostCenter::create($validated);

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost center created successfully.');
    }

    public function show(CostCenter $costCenter)
    {
        $this->authorizeCompany($costCenter);
        $costCenter->load(['branch', 'department']);

        return view('cost-centers.show', compact('costCenter'));
    }

    public function edit(CostCenter $costCenter)
    {
        $this->authorizeCompany($costCenter);
        $companyId = auth()->user()->company_id;

        $branches = Branch::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cost-centers.edit', compact('costCenter', 'branches', 'departments'));
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        $this->authorizeCompany($costCenter);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        $costCenter->update($validated);

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost center updated successfully.');
    }

    public function destroy(CostCenter $costCenter)
    {
        $this->authorizeCompany($costCenter);
        $costCenter->delete();

        return redirect()->route('cost-centers.index')
            ->with('success', 'Cost center deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
