@extends('layouts.app')
@section('title', 'Sales Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Sales Invoices</h1>
    <a href="{{ route('sales-invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Invoice
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('sales-invoices.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                    <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Invoice Type</label>
                <select name="invoice_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Cash" {{ request('invoice_type') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Credit" {{ request('invoice_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('sales-invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices ?? [] as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</td>
                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</td>
                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                    <td><span class="badge {{ $invoice->invoice_type == 'Cash' ? 'bg-success' : 'bg-info' }}">{{ $invoice->invoice_type }}</span></td>
                    <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                    <td>
                        @php
                            $badgeClass = match($invoice->status) {
                                'Draft' => 'bg-secondary',
                                'Sent' => 'bg-info',
                                'Partially Paid' => 'bg-warning text-dark',
                                'Paid' => 'bg-success',
                                'Overdue' => 'bg-danger',
                                'Cancelled' => 'bg-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $invoice->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('sales-invoices.show', $invoice) }}" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('sales-invoices.edit', $invoice) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('sales-invoices.print', $invoice) }}" class="btn btn-outline-dark" title="Print" target="_blank"><i class="bi bi-printer"></i></a>
                            <form action="{{ route('sales-invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this invoice?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">No invoices found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($invoices ?? collect(), 'links'))
    <div class="card-footer">{{ $invoices->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
