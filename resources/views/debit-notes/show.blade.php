@extends('layouts.app')
@section('title', 'Debit Note #' . $debitNote->debit_note_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Debit Note #{{ $debitNote->debit_note_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('debit-notes.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('debit-notes.edit', $debitNote) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Debit Note Details</h5>
        <span class="badge {{ $debitNote->status == 'Applied' ? 'bg-success' : ($debitNote->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">{{ $debitNote->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Supplier</p><p class="fw-bold">{{ $debitNote->supplier->name ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Purchase Invoice</p><p class="fw-bold">{{ $debitNote->invoice->invoice_number ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($debitNote->date)->format('d M Y') }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Amount</p><p class="fw-bold">{{ number_format($debitNote->amount, 2) }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Tax Amount</p><p class="fw-bold">{{ number_format($debitNote->tax_amount, 2) }}</p></div>
        </div>
        <div class="row mt-3">
            <div class="col-12"><p class="mb-1 text-muted">Reason</p><p>{{ $debitNote->reason }}</p></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body d-flex gap-2">
        @if($debitNote->status === 'Pending')
        <form action="{{ route('debit-notes.apply', $debitNote) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Apply Debit Note</button>
        </form>
        @endif
        <form action="{{ route('debit-notes.destroy', $debitNote) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
