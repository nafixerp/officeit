@extends('layouts.app')
@section('title', 'Sales Order #' . $salesOrder->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Sales Order #{{ $salesOrder->order_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('sales-orders.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Order Details</h5>
        @php
            $badgeClass = match($salesOrder->status) {
                'Draft' => 'bg-secondary',
                'Confirmed' => 'bg-info',
                'In Progress' => 'bg-warning text-dark',
                'Completed' => 'bg-success',
                'Cancelled' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $salesOrder->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Customer</p>
                <p class="fw-bold">{{ $salesOrder->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($salesOrder->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Currency</p>
                <p class="fw-bold">{{ $salesOrder->currency }} (Rate: {{ $salesOrder->exchange_rate }})</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Branch</p>
                <p class="fw-bold">{{ $salesOrder->branch->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Quotation Ref</p>
                <p class="fw-bold">{{ $salesOrder->quotation->quotation_number ?? '-' }}</p>
            </div>
        </div>
        @if($salesOrder->remarks)
        <div class="row mt-2">
            <div class="col-12">
                <p class="mb-1 text-muted">Remarks</p>
                <p>{{ $salesOrder->remarks }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Items --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Line Items</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item / Service</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesOrder->items as $idx => $item)
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

{{-- Totals --}}
<div class="row justify-content-end mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Subtotal:</td><td class="text-end">{{ number_format($salesOrder->subtotal, 2) }}</td></tr>
                    <tr><td>Total Tax:</td><td class="text-end">{{ number_format($salesOrder->total_tax, 2) }}</td></tr>
                    <tr><td>Total Discount:</td><td class="text-end">{{ number_format($salesOrder->total_discount, 2) }}</td></tr>
                    <tr class="table-primary fw-bold"><td>Grand Total:</td><td class="text-end">{{ $salesOrder->currency }} {{ number_format($salesOrder->total_amount, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="card">
    <div class="card-body d-flex gap-2 flex-wrap">
        @if($salesOrder->status === 'Confirmed' || $salesOrder->status === 'In Progress')
        <a href="{{ route('sales-orders.convert-to-invoice', $salesOrder) }}" class="btn btn-success"><i class="bi bi-receipt"></i> Convert to Invoice</a>
        @endif
        @if($salesOrder->status === 'Draft')
        <form action="{{ route('sales-orders.confirm', $salesOrder) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Confirm Order</button>
        </form>
        @endif
        <form action="{{ route('sales-orders.destroy', $salesOrder) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this sales order?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
