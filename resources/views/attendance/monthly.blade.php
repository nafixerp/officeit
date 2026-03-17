@extends('layouts.app')
@section('title', 'Monthly Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Monthly Attendance View</h1>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.monthly') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search"></i> View</button>
            </div>
        </form>
    </div>
</div>

@if(isset($selectedEmployee))
<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white text-center">
            <div class="card-body"><h3>{{ $summary['present'] ?? 0 }}</h3><p class="mb-0">Present</p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white text-center">
            <div class="card-body"><h3>{{ $summary['absent'] ?? 0 }}</h3><p class="mb-0">Absent</p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark text-center">
            <div class="card-body"><h3>{{ $summary['half_day'] ?? 0 }}</h3><p class="mb-0">Half Day</p></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white text-center">
            <div class="card-body"><h3>{{ $summary['leave'] ?? 0 }}</h3><p class="mb-0">On Leave</p></div>
        </div>
    </div>
</div>

<!-- Calendar Grid -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ $selectedEmployee->full_name }} - {{ date('F', mktime(0, 0, 0, request('month', date('n')), 1)) }} {{ request('year', date('Y')) }}</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $month = request('month', date('n'));
                        $year = request('year', date('Y'));
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
                        $dayCount = 0;
                        $attendanceMap = collect($monthlyData ?? [])->keyBy(function($item) {
                            return $item->date->format('j');
                        });
                    @endphp
                    <tr>
                    @for($i = 0; $i < $firstDay; $i++)
                        <td class="bg-light"></td>
                        @php $dayCount++; @endphp
                    @endfor
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $record = $attendanceMap[$day] ?? null;
                            $bgClass = '';
                            if($record) {
                                $bgClass = match($record->status) {
                                    'present' => 'bg-success bg-opacity-25',
                                    'absent' => 'bg-danger bg-opacity-25',
                                    'half_day' => 'bg-warning bg-opacity-25',
                                    'leave' => 'bg-info bg-opacity-25',
                                    default => ''
                                };
                            }
                        @endphp
                        <td class="{{ $bgClass }}" style="height:80px;vertical-align:top;">
                            <strong>{{ $day }}</strong>
                            @if($record)
                                <br><small class="badge bg-{{ ['present'=>'success','absent'=>'danger','half_day'=>'warning','leave'=>'info'][$record->status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_',' ',$record->status)) }}
                                </small>
                                @if($record->check_in)
                                    <br><small class="text-muted">{{ $record->check_in }} - {{ $record->check_out }}</small>
                                @endif
                            @endif
                        </td>
                        @php $dayCount++; @endphp
                        @if($dayCount % 7 == 0 && $day < $daysInMonth)
                            </tr><tr>
                        @endif
                    @endfor
                    @while($dayCount % 7 != 0)
                        <td class="bg-light"></td>
                        @php $dayCount++; @endphp
                    @endwhile
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
