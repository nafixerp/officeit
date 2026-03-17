@extends('layouts.app')
@section('title', 'Payment #' . $payment->payment_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Payment #{{ $payment->payment_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-primary">Edit</a>
        <a href="{{ route('payments.print', $payment) }}" class="btn btn-outline-dark" target="_blank">Print</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Payment Details</h5>
        <span class="badge {{ $payment->status == 'Confirmed' ? 'bg-success' : 'bg-secondary' }} fs-6">{{ $payment->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Paid To</p><p class="fw-bold">{{ $payment->supplier->name ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Mode</p><p class="fw-bold">{{ $payment->payment_mode }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Currency</p><p class="fw-bold">{{ $payment->currency }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Amount</p><p class="fw-bold fs-5 text-danger">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</p></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><p class="mb-1 text-muted">Reference #</p><p class="fw-bold">{{ $payment->reference_number ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Bank Account</p><p class="fw-bold">{{ $payment->bankAccount->name ?? '-' }}</p></div>
            <div class="col-md-6"><p class="mb-1 text-muted">Narration</p><p>{{ $payment->narration ?? '-' }}</p></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Invoice Allocations</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr><th>Invoice #</th><th>Supplier Inv #</th><th>Date</th><th class="text-end">Invoice Total</th><th class="text-end">Allocated Amount</th></tr>
            </thead>
            <tbody>
                @forelse($payment->allocations ?? [] as $alloc)
                <tr>
                    <td>{{ $alloc->invoice->invoice_number ?? '-' }}</td>
                    <td>{{ $alloc->invoice->supplier_invoice_number ?? '-' }}</td>
                    <td>{{ $alloc->invoice ? \Carbon\Carbon::parse($alloc->invoice->date)->format('d M Y') : '-' }}</td>
                    <td class="text-end">{{ number_format($alloc->invoice->total_amount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($alloc->amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No allocations.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
