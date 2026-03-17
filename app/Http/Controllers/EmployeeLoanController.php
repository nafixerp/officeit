<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use Illuminate\Http\Request;

class EmployeeLoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $loans = EmployeeLoan::whereHas('employee', fn ($q) => $q->where('company_id', $companyId))
            ->with('employee')
            ->latest()
            ->paginate(25);

        return view('employee-loans.index', compact('loans'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('employee-loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'monthly_deduction' => 'required|numeric|min:0',
            'start_date' => 'required|date',
        ]);

        $employee = Employee::where('id', $validated['employee_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        EmployeeLoan::create([
            'employee_id' => $employee->id,
            'loan_type' => $validated['loan_type'],
            'amount' => $validated['amount'],
            'monthly_deduction' => $validated['monthly_deduction'],
            'balance' => $validated['amount'],
            'start_date' => $validated['start_date'],
            'status' => 'ACTIVE',
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('employee-loans.index')->with('success', 'Employee loan created successfully.');
    }

    public function show(EmployeeLoan $employeeLoan)
    {
        $this->authorizeCompany($employeeLoan);

        $employeeLoan->load('employee');

        return view('employee-loans.show', compact('employeeLoan'));
    }

    public function edit(EmployeeLoan $employeeLoan)
    {
        $this->authorizeCompany($employeeLoan);

        $companyId = auth()->user()->company_id;
        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('employee-loans.edit', compact('employeeLoan', 'employees'));
    }

    public function update(Request $request, EmployeeLoan $employeeLoan)
    {
        $this->authorizeCompany($employeeLoan);

        $validated = $request->validate([
            'loan_type' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'monthly_deduction' => 'required|numeric|min:0',
            'balance' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'status' => 'required|in:ACTIVE,COMPLETED,CANCELLED',
        ]);

        $employeeLoan->update($validated);

        return redirect()->route('employee-loans.index')->with('success', 'Employee loan updated successfully.');
    }

    public function destroy(EmployeeLoan $employeeLoan)
    {
        $this->authorizeCompany($employeeLoan);

        $employeeLoan->delete();

        return redirect()->route('employee-loans.index')->with('success', 'Employee loan deleted successfully.');
    }

    protected function authorizeCompany(EmployeeLoan $loan): void
    {
        $loan->loadMissing('employee');

        if ($loan->employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
