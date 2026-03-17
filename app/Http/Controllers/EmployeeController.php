<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)
            ->when($request->department_id, fn ($q, $v) => $q->where('department_id', $v))
            ->when($request->designation_id, fn ($q, $v) => $q->where('designation_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->branch_id, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('full_name', 'like', "%{$v}%")
                  ->orWhere('employee_id_number', 'like', "%{$v}%");
            }))
            ->with(['department', 'designation', 'branch'])
            ->latest()
            ->paginate(25);

        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();
        $designations = Designation::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return view('employees.index', compact('employees', 'departments', 'designations', 'branches'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();
        $designations = Designation::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('employees.create', compact('departments', 'designations', 'branches', 'countries'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id_number' => 'required|string|max:255|unique:employees,employee_id_number,NULL,id,company_id,' . $companyId,
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'nationality' => 'nullable|string|max:255',
            'passport_number' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'visa_details' => 'nullable|string',
            'mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'joining_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_iban' => 'nullable|string|max:255',
            'status' => 'nullable|in:ACTIVE,PROBATION,RESIGNED,TERMINATED',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $validated['company_id'] = $companyId;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $this->authorizeCompany($employee);

        $employee->load([
            'department',
            'designation',
            'branch',
            'country',
            'documents',
            'leaveRequests.leaveType',
            'salaryStructures',
        ]);

        $leaveBalances = [];
        if ($employee->relationLoaded('leaveRequests')) {
            $leaveBalances = $employee->leaveRequests
                ->where('status', 'APPROVED')
                ->groupBy('leave_type_id')
                ->map(fn ($requests) => $requests->sum('days'));
        }

        return view('employees.show', compact('employee', 'leaveBalances'));
    }

    public function edit(Employee $employee)
    {
        $this->authorizeCompany($employee);

        $companyId = auth()->user()->company_id;

        $departments = Department::where('company_id', $companyId)->where('is_active', true)->get();
        $designations = Designation::where('company_id', $companyId)->where('is_active', true)->get();
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments', 'designations', 'branches', 'countries'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeCompany($employee);

        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id_number' => 'required|string|max:255|unique:employees,employee_id_number,' . $employee->id . ',id,company_id,' . $companyId,
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'nationality' => 'nullable|string|max:255',
            'passport_number' => 'nullable|string|max:255',
            'national_id' => 'nullable|string|max:255',
            'visa_details' => 'nullable|string',
            'mobile' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'joining_date' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_iban' => 'nullable|string|max:255',
            'status' => 'nullable|in:ACTIVE,PROBATION,RESIGNED,TERMINATED',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeCompany($employee);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    protected function authorizeCompany(Employee $employee): void
    {
        if ($employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
