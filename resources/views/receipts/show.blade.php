@extends('layouts.app')
@section('title', 'Receipt #' . $receipt->receipt_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Receipt #{{ $receipt->receipt_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('receipts.edit', $receipt) }}" class="btn btn-outline-primary">Edit</a>
        <a href="{{ route('receipts.print', $receipt) }}" class="btn btn-outline-dark" target="_blank">Print</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Receipt Details</h5>
        <span class="badge {{ $receipt->status == 'Confirmed' ? 'bg-success' : 'bg-secondary' }} fs-6">{{ $receipt->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Received From</p><p class="fw-bold">{{ $receipt->customer->name ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Mode</p><p class="fw-bold">{{ $receipt->receipt_mode }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Currency</p><p class="fw-bold">{{ $receipt->currency }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Amount</p><p class="fw-bold fs-5 text-success">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</p></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><p class="mb-1 text-muted">Reference #</p><p class="fw-bold">{{ $receipt->reference_number ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Bank Account</p><p class="fw-bold">{{ $receipt->bankAccount->name ?? '-' }}</p></div>
            <div class="col-md-6"><p class="mb-1 text-muted">Narration</p><p>{{ $receipt->narration ?? '-' }}</p></div>
        </div>
    </div>
</div>

{{-- Allocations --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Invoice Allocations</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr><th>Invoice #</th><th>Date</th><th class="text-end">Invoice Total</th><th class="text-end">Allocated Amount</th></tr>
            </thead>
            <tbody>
                @forelse($receipt->allocations ?? [] as $alloc)
                <tr>
                    <td>{{ $alloc->invoice->invoice_number ?? '-' }}</td>
                    <td>{{ $alloc->invoice ? \Carbon\Carbon::parse($alloc->invoice->date)->format('d M Y') : '-' }}</td>
                    <td class="text-end">{{ number_format($alloc->invoice->total_amount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($alloc->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No allocations.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
