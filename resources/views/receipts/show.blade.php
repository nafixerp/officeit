@extends('layouts.app')
@section('title', 'Receipt #' . $receipt->receipt_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Receipt #{{ $receipt->receipt_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('receipts.edit', $receipt) }}" class="btn btn-outline-primary">Edit</a>
        <a href="{{ route('receipts.print', $receipt) }}" class="btn btn-outline-dark" target="_blank"><i class="fas fa-print"></i> Print</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Receipt Details</h5>
        @php
            $badgeClass = match($receipt->status) {
                'Draft' => 'bg-secondary', 'Confirmed' => 'bg-success',
                'Cancelled' => 'bg-danger', default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $receipt->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Receipt Number</p>
                <p class="fw-bold">{{ $receipt->receipt_number }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Customer</p>
                <p class="fw-bold">{{ $receipt->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Receipt Mode</p>
                <p class="fw-bold">{{ $receipt->receipt_mode }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Currency</p>
                <p class="fw-bold">{{ $receipt->currency ?? 'USD' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Exchange Rate</p>
                <p class="fw-bold">{{ $receipt->exchange_rate ?? 1 }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Amount</p>
                <p class="fw-bold fs-5 text-success">{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->amount, 2) }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Reference Number</p>
                <p class="fw-bold">{{ $receipt->reference_number ?? '-' }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Bank Account</p>
                <p class="fw-bold">{{ $receipt->bankAccount->account_name ?? '-' }}</p>
            </div>
            <div class="col-md-9">
                <p class="mb-1 text-muted">Narration</p>
                <p class="fw-bold">{{ $receipt->narration ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
