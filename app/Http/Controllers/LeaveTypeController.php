<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $leaveTypes = LeaveType::where('company_id', $companyId)
            ->latest()
            ->paginate(25);

        return view('leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['is_paid'] = $request->boolean('is_paid', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        LeaveType::create($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function show(LeaveType $leaveType)
    {
        $this->authorizeCompany($leaveType);

        return view('leave-types.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType)
    {
        $this->authorizeCompany($leaveType);

        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $this->authorizeCompany($leaveType);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'days_per_year' => 'required|integer|min:0',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_paid'] = $request->boolean('is_paid', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->authorizeCompany($leaveType);

        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('success', 'Leave type deleted successfully.');
    }

    protected function authorizeCompany(LeaveType $leaveType): void
    {
        if ($leaveType->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
