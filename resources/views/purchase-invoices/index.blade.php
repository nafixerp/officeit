@extends('layouts.app')
@section('title', 'Purchase Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Purchase Invoices</h1>
    <a href="{{ route('purchase-invoices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Purchase Invoice</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('purchase-invoices.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                    <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers ?? [] as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
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
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('purchase-invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Supplier Inv #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Supplier</th>
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
                    <td>{{ $invoice->supplier_invoice_number ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</td>
                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</td>
                    <td>{{ $invoice->supplier->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                    <td>
                        @php
                            $badgeClass = match($invoice->status) {
                                'Draft' => 'bg-secondary', 'Received' => 'bg-info', 'Partially Paid' => 'bg-warning text-dark',
                                'Paid' => 'bg-success', 'Overdue' => 'bg-danger', 'Cancelled' => 'bg-dark', default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $invoice->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('purchase-invoices.show', $invoice) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('purchase-invoices.edit', $invoice) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('purchase-invoices.print', $invoice) }}" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer"></i></a>
                            <form action="{{ route('purchase-invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No purchase invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($invoices ?? collect(), 'links'))
    <div class="card-footer">{{ $invoices->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
