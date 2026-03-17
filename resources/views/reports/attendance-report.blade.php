@extends('layouts.app')
@section('title', 'Attendance Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Attendance Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="fas fa-print"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/attendance-report') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (request('month', date('n')) == $m) ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ (request('year', date('Y')) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name ?? ($employee->first_name . ' ' . $employee->last_name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

@php
    $month = request('month', date('n'));
    $year = request('year', date('Y'));
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
@endphp

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" style="font-size: 0.8rem;">
                <thead class="table-light">
                    <tr>
                        <th class="sticky-start">Employee</th>
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            <th class="text-center" style="min-width: 35px;">{{ $d }}</th>
                        @endfor
                        <th class="text-center">P</th>
                        <th class="text-center">A</th>
                        <th class="text-center">L</th>
                        <th class="text-center">H</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceData ?? [] as $empData)
                    <tr>
                        <td class="fw-semibold text-nowrap">{{ $empData['employee_name'] ?? '-' }}</td>
                        @php
                            $presentCount = 0; $absentCount = 0; $leaveCount = 0; $holidayCount = 0;
                        @endphp
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $status = $empData['days'][$d] ?? '-';
                                $colorMap = ['P' => 'text-success', 'A' => 'text-danger', 'H' => 'text-primary', 'L' => 'text-warning'];
                                if($status == 'P') $presentCount++;
                                elseif($status == 'A') $absentCount++;
                                elseif($status == 'L') $leaveCount++;
                                elseif($status == 'H') $holidayCount++;
                            @endphp
                            <td class="text-center {{ $colorMap[$status] ?? '' }}">{{ $status }}</td>
                        @endfor
                        <td class="text-center fw-bold text-success">{{ $presentCount }}</td>
                        <td class="text-center fw-bold text-danger">{{ $absentCount }}</td>
                        <td class="text-center fw-bold text-warning">{{ $leaveCount }}</td>
                        <td class="text-center fw-bold text-primary">{{ $holidayCount }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ $daysInMonth + 5 }}" class="text-center text-muted py-4">No attendance data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <small class="text-muted">
            <strong>P</strong> = Present &nbsp;
            <strong class="text-danger">A</strong> = Absent &nbsp;
            <strong class="text-primary">H</strong> = Holiday &nbsp;
            <strong class="text-warning">L</strong> = Leave
        </small>
    </div>
</div>
@endsection
