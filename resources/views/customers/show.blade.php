@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Customer: {{ $customer->name }}</h1>
    <div>
        <a href="{{ route('customers.ledger', $customer) }}" class="btn btn-primary">Ledger</a>
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Customer Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Code:</strong><br>{{ $customer->code }}</div>
                    <div class="col-md-6 mb-2"><strong>Name:</strong><br>{{ $customer->name }}</div>
                    <div class="col-md-6 mb-2"><strong>Company:</strong><br>{{ $customer->company_name ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Type:</strong><br>{{ $customer->customer_type ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Contact Person:</strong><br>{{ $customer->contact_person ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Salesperson:</strong><br>{{ $customer->salesperson ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Phone:</strong><br>{{ $customer->phone ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Mobile:</strong><br>{{ $customer->mobile ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Email:</strong><br>{{ $customer->email ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Country:</strong><br>{{ $customer->country->name ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Currency:</strong><br>{{ $customer->currency->code ?? '-' }}</div>
                    <div class="col-md-6 mb-2"><strong>Tax Number:</strong><br>{{ $customer->tax_number ?? '-' }}</div>
                    <div class="col-12 mb-2"><strong>Address:</strong><br>{{ $customer->address ?? '-' }}</div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Financial Summary</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Credit Days:</strong> {{ $customer->credit_days ?? 0 }}</div>
                <div class="mb-2"><strong>Credit Limit:</strong> {{ number_format($customer->credit_limit ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Opening Balance:</strong> {{ number_format($customer->opening_balance ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

@if(isset($recentInvoices) && $recentInvoices->count())
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">Recent Invoices</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
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

@if(isset($recentReceipts) && $recentReceipts->count())
<div class="card mb-3">
    <div class="card-header"><h5 class="mb-0">Recent Receipts</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentReceipts as $receipt)
                        <tr>
                            <td>{{ $receipt->receipt_number }}</td>
                            <td>{{ $receipt->receipt_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($receipt->amount, 2) }}</td>
                            <td>{{ $receipt->payment_method ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
