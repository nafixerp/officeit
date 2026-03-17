@extends('layouts.app')
@section('title', 'Purchase Return #' . $purchaseReturn->return_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Purchase Return #{{ $purchaseReturn->return_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('purchase-returns.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('purchase-returns.edit', $purchaseReturn) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Return Details</h5>
        @php
            $badgeClass = match($purchaseReturn->status ?? 'Pending') {
                'Pending' => 'bg-warning text-dark',
                'Approved' => 'bg-success',
                'Cancelled' => 'bg-danger',
                default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $purchaseReturn->status ?? 'Pending' }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Return Number</p>
                <p class="fw-bold">{{ $purchaseReturn->return_number }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($purchaseReturn->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Supplier</p>
                <p class="fw-bold">{{ $purchaseReturn->supplier->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Purchase Invoice</p>
                <p class="fw-bold">
                    @if($purchaseReturn->invoice)
                        <a href="{{ route('purchase-invoices.show', $purchaseReturn->invoice) }}">#{{ $purchaseReturn->invoice->invoice_number }}</a>
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Amount</p>
                <p class="fw-bold fs-5 text-danger">{{ number_format($purchaseReturn->amount, 2) }}</p>
            </div>
            <div class="col-md-9">
                <p class="mb-1 text-muted">Reason</p>
                <p class="fw-bold">{{ $purchaseReturn->reason ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
