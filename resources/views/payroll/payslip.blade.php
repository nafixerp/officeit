@extends('layouts.app')
@section('title', 'Payslip')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .payslip-container { border: 1px solid #000; }
    }
    .payslip-container { max-width: 800px; margin: 0 auto; }
</style>
@endpush

@section('content')
<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
    <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="payslip-container bg-white p-4 border">
    <!-- Company Header -->
    <div class="text-center border-bottom pb-3 mb-3">
        <h2 class="mb-1">{{ $company->name ?? config('app.name', 'OfficeIT') }}</h2>
        <p class="text-muted mb-0">{{ $company->address ?? '' }}</p>
        <p class="text-muted mb-0">{{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
        <h4 class="mt-3 mb-0">PAYSLIP</h4>
        <p class="text-muted">{{ date('F', mktime(0,0,0,$payroll->month,1)) }} {{ $payroll->year }}</p>
    </div>

    <!-- Employee Details -->
    <div class="row mb-4">
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td class="text-muted">Employee Name</td><td class="fw-bold">{{ $payroll->employee->full_name ?? '-' }}</td></tr>
                <tr><td class="text-muted">Employee ID</td><td>{{ $payroll->employee->employee_id_number ?? '-' }}</td></tr>
                <tr><td class="text-muted">Department</td><td>{{ $payroll->employee->department->name ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td class="text-muted">Designation</td><td>{{ $payroll->employee->designation->name ?? '-' }}</td></tr>
                <tr><td class="text-muted">Joining Date</td><td>{{ $payroll->employee->joining_date?->format('d M Y') ?? '-' }}</td></tr>
                <tr><td class="text-muted">Payment Date</td><td>{{ $payroll->payment_date?->format('d M Y') ?? '-' }}</td></tr>
            </table>
        </div>
    </div>

    <!-- Earnings and Deductions -->
    <div class="row mb-4">
        <div class="col-6">
            <table class="table table-bordered table-sm">
                <thead class="table-success">
                    <tr><th colspan="2">Earnings</th></tr>
                </thead>
                <tbody>
                    @foreach($payroll->earnings ?? [] as $earning)
                    <tr><td>{{ $earning->component }}</td><td class="text-end">{{ number_format($earning->amount, 2) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold"><td>Total Earnings</td><td class="text-end">{{ number_format($payroll->total_earnings ?? 0, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-bordered table-sm">
                <thead class="table-danger">
                    <tr><th colspan="2">Deductions</th></tr>
                </thead>
                <tbody>
                    @foreach($payroll->deductions ?? [] as $deduction)
                    <tr><td>{{ $deduction->component }}</td><td class="text-end">{{ number_format($deduction->amount, 2) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold"><td>Total Deductions</td><td class="text-end">{{ number_format($payroll->total_deductions ?? 0, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Net Salary -->
    <div class="border-top border-bottom py-3 mb-4 text-center">
        <h4 class="mb-0">Net Salary: <span class="text-primary">{{ number_format($payroll->net_salary ?? 0, 2) }}</span></h4>
    </div>

    <!-- Bank Details -->
    <div class="mb-4">
        <h6>Bank Details</h6>
        <table class="table table-borderless table-sm">
            <tr><td class="text-muted" style="width:30%">Bank Name</td><td>{{ $payroll->employee->bank_name ?? '-' }}</td></tr>
            <tr><td class="text-muted">Account Number</td><td>{{ $payroll->employee->bank_account_number ?? '-' }}</td></tr>
            <tr><td class="text-muted">IBAN</td><td>{{ $payroll->employee->bank_iban ?? '-' }}</td></tr>
        </table>
    </div>

    <!-- Signature Area -->
    <div class="row mt-5 pt-5">
        <div class="col-6 text-center">
            <div class="border-top d-inline-block" style="width:200px;"></div>
            <p class="mb-0">Employee Signature</p>
        </div>
        <div class="col-6 text-center">
            <div class="border-top d-inline-block" style="width:200px;"></div>
            <p class="mb-0">Authorized Signature</p>
        </div>
    </div>
</div>
@endsection
