@extends('layouts.app')
@section('title', 'Payroll Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payroll Details</h1>
    <div>
        <a href="{{ route('payroll.payslip', $payroll) }}" class="btn btn-primary"><i class="bi bi-printer"></i> Print Payslip</a>
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Employee Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Employee</td><td>{{ $payroll->employee->full_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Employee ID</td><td>{{ $payroll->employee->employee_id_number ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Department</td><td>{{ $payroll->employee->department->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Designation</td><td>{{ $payroll->employee->designation->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Period</td><td>{{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>
                        @php $statusColors = ['draft' => 'secondary', 'confirmed' => 'primary', 'paid' => 'success']; @endphp
                        <span class="badge bg-{{ $statusColors[$payroll->status] ?? 'secondary' }}">{{ ucfirst($payroll->status) }}</span>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Bank Details</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted" style="width:40%">Bank Name</td><td>{{ $payroll->employee->bank_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Account Number</td><td>{{ $payroll->employee->bank_account_number ?? '-' }}</td></tr>
                    <tr><td class="text-muted">IBAN</td><td>{{ $payroll->employee->bank_iban ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Earnings</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Component</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                        @foreach($payroll->earnings ?? [] as $earning)
                        <tr>
                            <td>{{ $earning->component }}</td>
                            <td class="text-end">{{ number_format($earning->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold">Total Earnings</td>
                            <td class="text-end fw-bold">{{ number_format($payroll->total_earnings ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white"><h5 class="mb-0">Deductions</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>Component</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                        @foreach($payroll->deductions ?? [] as $deduction)
                        <tr>
                            <td>{{ $deduction->component }}</td>
                            <td class="text-end">{{ number_format($deduction->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td class="fw-bold">Total Deductions</td>
                            <td class="text-end fw-bold">{{ number_format($payroll->total_deductions ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body text-center">
        <h4>Net Salary: <span class="text-primary">{{ number_format($payroll->net_salary ?? 0, 2) }}</span></h4>
    </div>
</div>
@endsection
