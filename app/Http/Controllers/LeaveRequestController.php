<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $leaveRequests = LeaveRequest::whereHas('employee', fn ($q) => $q->where('company_id', $companyId))
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->from_date, fn ($q, $v) => $q->where('start_date', '>=', $v))
            ->when($request->to_date, fn ($q, $v) => $q->where('end_date', '<=', $v))
            ->with(['employee', 'leaveType', 'approvedBy'])
            ->latest()
            ->paginate(25);

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('leave-requests.index', compact('leaveRequests', 'employees'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();
        $leaveTypes = LeaveType::where('company_id', $companyId)->where('is_active', true)->get();

        return view('leave-requests.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $employee = Employee::where('id', $validated['employee_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $leaveType = LeaveType::where('id', $validated['leave_type_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $usedDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->whereIn('status', ['PENDING', 'APPROVED'])
            ->whereYear('start_date', $startDate->year)
            ->sum('days');

        $remainingBalance = $leaveType->days_per_year - $usedDays;

        if ($days > $remainingBalance) {
            return back()->withErrors(['days' => "Insufficient leave balance. Available: {$remainingBalance} days."])->withInput();
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'status' => 'PENDING',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $leaveRequest->load(['employee', 'leaveType', 'approvedBy']);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function edit(LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();
        $leaveTypes = LeaveType::where('company_id', $companyId)->where('is_active', true)->get();

        return view('leave-requests.edit', compact('leaveRequest', 'employees', 'leaveTypes'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $leaveRequest->update([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('success', 'Leave request deleted successfully.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $leaveRequest->update([
            'status' => 'APPROVED',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeCompany($leaveRequest);

        $leaveRequest->update([
            'status' => 'REJECTED',
            'approved_by' => auth()->id(),
            'remarks' => $request->input('remarks'),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }

    protected function authorizeCompany(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing('employee');

        if ($leaveRequest->employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
