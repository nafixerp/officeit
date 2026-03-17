@extends('layouts.app')
@section('title', 'Payroll Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payroll Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="fas fa-print"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/payroll-report') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (request('month', date('n')) == $m) ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ (request('year', date('Y')) == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th class="text-end">Basic Salary</th>
                    <th class="text-end">Earnings</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Net Salary</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrollData ?? [] as $data)
                <tr>
                    <td>{{ $data['employee_name'] ?? $data['name'] ?? '-' }}</td>
                    <td class="text-end">{{ number_format($data['basic_salary'] ?? 0, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($data['earnings'] ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($data['deductions'] ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($data['net_salary'] ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No payroll data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td class="fw-bold">Grand Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrollData ?? [])->sum('basic_salary'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrollData ?? [])->sum('earnings'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrollData ?? [])->sum('deductions'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrollData ?? [])->sum('net_salary'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
