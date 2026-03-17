@extends('layouts.app')
@section('title', 'Purchase Order #' . $purchaseOrder->order_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Purchase Order #{{ $purchaseOrder->order_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Order Details</h5>
        @php
            $badgeClass = match($purchaseOrder->status) {
                'Draft' => 'bg-secondary', 'Confirmed' => 'bg-info', 'Received' => 'bg-primary',
                'Completed' => 'bg-success', 'Cancelled' => 'bg-danger', default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $purchaseOrder->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Supplier</p><p class="fw-bold">{{ $purchaseOrder->supplier->name ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($purchaseOrder->date)->format('d M Y') }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Currency</p><p class="fw-bold">{{ $purchaseOrder->currency }} (Rate: {{ $purchaseOrder->exchange_rate }})</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Branch</p><p class="fw-bold">{{ $purchaseOrder->branch->name ?? '-' }}</p></div>
        </div>
        @if($purchaseOrder->remarks)
        <div class="row mt-2"><div class="col-12"><p class="mb-1 text-muted">Remarks</p><p>{{ $purchaseOrder->remarks }}</p></div></div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Line Items</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Item</th><th>Description</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Discount</th><th class="text-end">Tax</th><th class="text-end">Net Amount</th></tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td><td>{{ $item->item->name ?? '-' }}</td><td>{{ $item->description }}</td>
                    <td class="text-end">{{ $item->quantity }}</td><td class="text-end">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount, 2) }}</td><td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->net_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row justify-content-end mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Subtotal:</td><td class="text-end">{{ number_format($purchaseOrder->subtotal, 2) }}</td></tr>
                    <tr><td>Total Tax:</td><td class="text-end">{{ number_format($purchaseOrder->total_tax, 2) }}</td></tr>
                    <tr><td>Total Discount:</td><td class="text-end">{{ number_format($purchaseOrder->total_discount, 2) }}</td></tr>
                    <tr class="table-primary fw-bold"><td>Grand Total:</td><td class="text-end">{{ $purchaseOrder->currency }} {{ number_format($purchaseOrder->total_amount, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body d-flex gap-2 flex-wrap">
        @if($purchaseOrder->status === 'Confirmed')
        <a href="{{ route('purchase-orders.convert-to-invoice', $purchaseOrder) }}" class="btn btn-success"><i class="bi bi-receipt"></i> Convert to Purchase Invoice</a>
        @endif
        @if($purchaseOrder->status === 'Draft')
        <form action="{{ route('purchase-orders.confirm', $purchaseOrder) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Confirm Order</button>
        </form>
        @endif
        <form action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
