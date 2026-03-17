<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $attendance = Attendance::whereHas('employee', fn ($q) => $q->where('company_id', $companyId))
            ->when($request->employee_id, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($request->date, fn ($q, $v) => $q->where('date', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->with('employee')
            ->latest('date')
            ->paginate(25);

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('attendance.index', compact('attendance', 'employees'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;

        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('attendance.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'shift' => 'nullable|string|max:255',
            'status' => 'required|in:PRESENT,ABSENT,HALF_DAY,LEAVE,HOLIDAY,LATE',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $employee = Employee::where('id', $validated['employee_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        Attendance::create($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance)
    {
        $this->authorizeCompany($attendance);

        $attendance->load('employee');

        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $this->authorizeCompany($attendance);

        $companyId = auth()->user()->company_id;
        $employees = Employee::where('company_id', $companyId)->where('status', 'ACTIVE')->get();

        return view('attendance.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorizeCompany($attendance);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'shift' => 'nullable|string|max:255',
            'status' => 'required|in:PRESENT,ABSENT,HALF_DAY,LEAVE,HOLIDAY,LATE',
            'overtime_hours' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        $attendance->update($validated);

        return redirect()->route('attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorizeCompany($attendance);

        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted successfully.');
    }

    public function bulkCreate(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            'date' => 'required|date',
            'default_status' => 'nullable|in:PRESENT,ABSENT,HALF_DAY,LEAVE,HOLIDAY,LATE',
        ]);

        $date = $validated['date'];
        $defaultStatus = $validated['default_status'] ?? 'PRESENT';

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->get();

        $existingEmployeeIds = Attendance::where('date', $date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->pluck('employee_id')
            ->toArray();

        $records = [];
        $now = now();

        foreach ($employees as $employee) {
            if (!in_array($employee->id, $existingEmployeeIds)) {
                $records[] = [
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'status' => $defaultStatus,
                    'overtime_hours' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($records)) {
            Attendance::insert($records);
        }

        return redirect()->route('attendance.index', ['date' => $date])
            ->with('success', count($records) . ' attendance records created.');
    }

    public function monthlyReport(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'ACTIVE')
            ->get();

        $attendance = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');

        $summary = $employees->map(function ($employee) use ($attendance) {
            $records = $attendance->get($employee->id, collect());

            return [
                'employee' => $employee,
                'present' => $records->where('status', 'PRESENT')->count(),
                'absent' => $records->where('status', 'ABSENT')->count(),
                'half_day' => $records->where('status', 'HALF_DAY')->count(),
                'leave' => $records->where('status', 'LEAVE')->count(),
                'holiday' => $records->where('status', 'HOLIDAY')->count(),
                'late' => $records->where('status', 'LATE')->count(),
                'overtime_hours' => $records->sum('overtime_hours'),
                'total_days' => $records->count(),
            ];
        });

        return view('attendance.monthly-report', compact('summary', 'month', 'year'));
    }

    protected function authorizeCompany(Attendance $attendance): void
    {
        $attendance->loadMissing('employee');

        if ($attendance->employee->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access.');
        }
    }
}
