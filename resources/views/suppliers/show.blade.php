@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Supplier: {{ $supplier->name }}</h1>
    <div>
        <a href="{{ route('suppliers.ledger', $supplier) }}" class="btn btn-primary">Ledger</a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Supplier Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Code:</strong><br>{{ $supplier->code }}</div>
                    <div class="col-md-6 mb-2"><strong>Name:</strong><br>{{ $supplier->name }}</div>
                    <div class="col-md-6 mb-2"><strong>Company:</strong><br>{{ $supplier->company_name ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Category:</strong><br>{{ $supplier->category ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Contact Person:</strong><br>{{ $supplier->contact_person ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Phone:</strong><br>{{ $supplier->phone ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Mobile:</strong><br>{{ $supplier->mobile ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Email:</strong><br>{{ $supplier->email ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Country:</strong><br>{{ $supplier->country->name ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Currency:</strong><br>{{ $supplier->currency->code ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Tax Number:</strong><br>{{ $supplier->tax_number ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Payment Terms:</strong><br>{{ $supplier->payment_terms ?? '-' }}</div>
                    <div class="col-12 mb-2"><strong>Address:</strong><br>{{ $supplier->address ?? '-' }}</div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Financial Summary</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Opening Balance:</strong> {{ number_format($supplier->opening_balance ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Bank Details</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Bank:</strong> {{ $supplier->bank_name ?? '-' }}</div>
                <div class="mb-2"><strong>Account #:</strong> {{ $supplier->bank_account_number ?? '-' }}</div>
                <div class="mb-2"><strong>SWIFT:</strong> {{ $supplier->bank_swift_code ?? '-' }}</div>
                <div class="mb-2"><strong>IBAN:</strong> {{ $supplier->bank_iban ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

@if(isset($recentInvoices) && $recentInvoices->count())
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">Recent Purchase Invoices</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Invoice #</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentInvoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td><span class="badge bg-info">{{ $invoice->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if(isset($recentPayments) && $recentPayments->count())
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">Recent Payments</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Payment #</th><th>Date</th><th>Amount</th><th>Method</th></tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->payment_number }}</td>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
