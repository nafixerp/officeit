@extends('layouts.app')
@section('title', 'Attendance Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Monthly Attendance Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/attendance') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Attendance Summary - {{ date('F', mktime(0,0,0,request('month', date('n')),1)) }} {{ request('year', date('Y')) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-center">Working Days</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Half Day</th>
                    <th class="text-center">On Leave</th>
                    <th class="text-center">Overtime (hrs)</th>
                    <th class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendanceData ?? [] as $data)
                <tr>
                    <td>{{ $data['employee_name'] ?? '-' }}</td>
                    <td>{{ $data['department'] ?? '-' }}</td>
                    <td class="text-center">{{ $data['working_days'] ?? 0 }}</td>
                    <td class="text-center text-success fw-bold">{{ $data['present'] ?? 0 }}</td>
                    <td class="text-center text-danger fw-bold">{{ $data['absent'] ?? 0 }}</td>
                    <td class="text-center text-warning fw-bold">{{ $data['half_day'] ?? 0 }}</td>
                    <td class="text-center text-info fw-bold">{{ $data['on_leave'] ?? 0 }}</td>
                    <td class="text-center">{{ $data['overtime_hours'] ?? 0 }}</td>
                    <td class="text-center">
                        @php
                            $wd = $data['working_days'] ?? 1;
                            $pct = $wd > 0 ? round((($data['present'] ?? 0) + (($data['half_day'] ?? 0) * 0.5)) / $wd * 100, 1) : 0;
                        @endphp
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar {{ $pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $pct }}%">{{ $pct }}%</div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No attendance data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
