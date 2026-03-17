@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Invoice #{{ $invoice->invoice_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('sales-invoices.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('sales-invoices.edit', $invoice) }}" class="btn btn-outline-primary">Edit</a>
        <a href="{{ route('sales-invoices.print', $invoice) }}" class="btn btn-outline-dark" target="_blank">Print</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Invoice Details</h5>
        @php
            $badgeClass = match($invoice->status) {
                'Draft' => 'bg-secondary', 'Sent' => 'bg-info', 'Partially Paid' => 'bg-warning text-dark',
                'Paid' => 'bg-success', 'Overdue' => 'bg-danger', 'Cancelled' => 'bg-dark', default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $invoice->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Customer</p>
                <p class="fw-bold">{{ $invoice->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Due Date</p>
                <p class="fw-bold">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Type</p>
                <p class="fw-bold">{{ $invoice->invoice_type }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Currency</p>
                <p class="fw-bold">{{ $invoice->currency }} (Rate: {{ $invoice->exchange_rate }})</p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Sales Order</p>
                <p class="fw-bold">{{ $invoice->salesOrder->order_number ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Branch</p>
                <p class="fw-bold">{{ $invoice->branch->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Project</p>
                <p class="fw-bold">{{ $invoice->project->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Cost Center</p>
                <p class="fw-bold">{{ $invoice->costCenter->name ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Items --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Line Items</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Item</th><th>Description</th>
                    <th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Discount</th>
                    <th class="text-end">Tax</th><th class="text-end">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->item->name ?? '-' }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->net_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Totals & Payment Summary --}}
<div class="row mb-4">
    <div class="col-md-6">
        {{-- Payment History --}}
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Payment History</h5></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Receipt #</th><th>Date</th><th>Mode</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->receipts ?? [] as $receipt)
                        <tr>
                            <td>{{ $receipt->receipt_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</td>
                            <td>{{ $receipt->receipt_mode }}</td>
                            <td class="text-end">{{ number_format($receipt->pivot->amount ?? $receipt->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No payments received yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Subtotal:</td><td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    <tr><td>Total Tax:</td><td class="text-end">{{ number_format($invoice->total_tax, 2) }}</td></tr>
                    <tr><td>Total Discount:</td><td class="text-end">{{ number_format($invoice->total_discount, 2) }}</td></tr>
                    <tr class="fw-bold"><td>Total Amount:</td><td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td></tr>
                    <tr class="text-success"><td>Paid Amount:</td><td class="text-end">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
                    <tr class="table-warning fw-bold"><td>Remaining Balance:</td><td class="text-end">{{ $invoice->currency }} {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="card">
    <div class="card-body d-flex gap-2 flex-wrap">
        @if($invoice->total_amount - $invoice->paid_amount > 0)
        <a href="{{ route('receipts.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success"><i class="bi bi-cash-coin"></i> Record Payment</a>
        @endif
        <a href="{{ route('sales-invoices.print', $invoice) }}" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer"></i> Print</a>
        <a href="{{ route('sales-invoices.email', $invoice) }}" class="btn btn-outline-info"><i class="bi bi-envelope"></i> Email</a>
    </div>
</div>
@endsection
