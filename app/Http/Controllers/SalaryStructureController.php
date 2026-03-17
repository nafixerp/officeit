<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryStructure;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Employee $employee)
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.component' => 'required|string|max:255',
            'components.*.type' => 'required|in:EARNING,DEDUCTION',
            'components.*.amount' => 'required|numeric|min:0',
            'components.*.percentage' => 'nullable|numeric|min:0|max:100',
            'components.*.is_active' => 'boolean',
        ]);

        // Remove existing salary structure for this employee
        SalaryStructure::where('employee_id', $employee->id)->delete();

        // Create new salary components
        foreach ($validated['components'] as $component) {
            SalaryStructure::create([
                'employee_id' => $employee->id,
                'component' => $component['component'],
                'type' => $component['type'],
                'amount' => $component['amount'],
                'percentage' => $component['percentage'] ?? null,
                'is_active' => $component['is_active'] ?? true,
            ]);
        }

        return back()->with('success', 'Salary structure saved successfully.');
    }
}
