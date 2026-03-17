<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $designations = Designation::where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(20);

        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Designation::create($validated);

        return redirect()->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function show(Designation $designation)
    {
        $this->authorizeCompany($designation);

        return view('designations.show', compact('designation'));
    }

    public function edit(Designation $designation)
    {
        $this->authorizeCompany($designation);

        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $this->authorizeCompany($designation);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $designation->update($validated);

        return redirect()->route('designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $this->authorizeCompany($designation);
        $designation->delete();

        return redirect()->route('designations.index')
            ->with('success', 'Designation deleted successfully.');
    }

    private function authorizeCompany($model)
    {
        if ($model->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
