@extends('layouts.app')
@section('title', 'Credit Note #' . $creditNote->credit_note_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Credit Note #{{ $creditNote->credit_note_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('credit-notes.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('credit-notes.edit', $creditNote) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Credit Note Details</h5>
        <span class="badge {{ $creditNote->status == 'Applied' ? 'bg-success' : ($creditNote->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">{{ $creditNote->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Customer</p>
                <p class="fw-bold">{{ $creditNote->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Invoice</p>
                <p class="fw-bold">{{ $creditNote->invoice->invoice_number ?? '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($creditNote->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Amount</p>
                <p class="fw-bold">{{ number_format($creditNote->amount, 2) }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Tax Amount</p>
                <p class="fw-bold">{{ number_format($creditNote->tax_amount, 2) }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <p class="mb-1 text-muted">Reason</p>
                <p>{{ $creditNote->reason }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body d-flex gap-2">
        @if($creditNote->status === 'Pending')
        <form action="{{ route('credit-notes.apply', $creditNote) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Apply Credit Note</button>
        </form>
        @endif
        <form action="{{ route('credit-notes.destroy', $creditNote) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
