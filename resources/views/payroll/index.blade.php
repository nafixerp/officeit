@extends('layouts.app')
@section('title', 'Payroll')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payroll</h1>
    <a href="{{ route('payroll.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Generate Payroll</a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payroll.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
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
                    <th>Month/Year</th>
                    <th class="text-end">Basic</th>
                    <th class="text-end">Earnings</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Net Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls ?? [] as $payroll)
                <tr>
                    <td>{{ $payroll->employee->full_name ?? '-' }}</td>
                    <td>{{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</td>
                    <td class="text-end">{{ number_format($payroll->basic_salary ?? 0, 2) }}</td>
                    <td class="text-end text-success">{{ number_format($payroll->total_earnings ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($payroll->total_deductions ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($payroll->net_salary ?? 0, 2) }}</td>
                    <td>
                        @php $statusColors = ['draft' => 'secondary', 'confirmed' => 'primary', 'paid' => 'success']; @endphp
                        <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}">{{ ucfirst($payroll->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('payroll.payslip', $payroll) }}" class="btn btn-outline-primary"><i class="bi bi-printer"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No payroll records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payrolls ?? collect(), 'links'))
    <div class="card-footer">{{ $payrolls->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
