@extends('layouts.app')
@section('title', 'Payroll Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Monthly Payroll Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/payroll') }}" class="row g-3">
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

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white text-center">
            <div class="card-body">
                <h4>{{ number_format($totalBasic ?? 0, 2) }}</h4>
                <p class="mb-0">Total Basic</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white text-center">
            <div class="card-body">
                <h4>{{ number_format($totalEarnings ?? 0, 2) }}</h4>
                <p class="mb-0">Total Earnings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white text-center">
            <div class="card-body">
                <h4>{{ number_format($totalDeductions ?? 0, 2) }}</h4>
                <p class="mb-0">Total Deductions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-dark text-white text-center">
            <div class="card-body">
                <h4>{{ number_format($totalNet ?? 0, 2) }}</h4>
                <p class="mb-0">Total Net Salary</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payroll Summary - {{ date('F', mktime(0,0,0,request('month', date('n')),1)) }} {{ request('year', date('Y')) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-end">Basic</th>
                    <th class="text-end">Earnings</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Net Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls ?? [] as $payroll)
                <tr>
                    <td>{{ $payroll->employee->full_name ?? '-' }}</td>
                    <td>{{ $payroll->employee->department->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($payroll->basic_salary ?? 0, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($payroll->total_earnings ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($payroll->total_deductions ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($payroll->net_salary ?? 0, 2) }}</td>
                    <td>
                        @php $statusColors = ['draft' => 'secondary', 'confirmed' => 'primary', 'paid' => 'success']; @endphp
                        <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}">{{ ucfirst($payroll->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No payroll data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="2" class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrolls ?? [])->sum('basic_salary'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrolls ?? [])->sum('total_earnings'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrolls ?? [])->sum('total_deductions'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($payrolls ?? [])->sum('net_salary'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
