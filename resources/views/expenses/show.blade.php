@extends('layouts.app')
@section('title', 'Expense Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Expense Details</h1>
    <div class="btn-group">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Expense Information</h5>
        <span class="badge {{ $expense->status == 'Approved' ? 'bg-success' : ($expense->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6">{{ $expense->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Category</p><p class="fw-bold">{{ $expense->category->name ?? '-' }}</p></div>
            <div class="col-md-6"><p class="mb-1 text-muted">Description</p><p class="fw-bold">{{ $expense->description }}</p></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2"><p class="mb-1 text-muted">Amount</p><p class="fw-bold">{{ number_format($expense->amount, 2) }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Tax Rate</p><p class="fw-bold">{{ $expense->tax_rate }}%</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Tax Amount</p><p class="fw-bold">{{ number_format($expense->tax_amount, 2) }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Total</p><p class="fw-bold fs-5 text-danger">{{ number_format($expense->total_amount, 2) }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Payment Mode</p><p class="fw-bold">{{ $expense->payment_mode }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Recurring</p><p class="fw-bold">{{ $expense->is_recurring ? 'Yes' : 'No' }}</p></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><p class="mb-1 text-muted">Branch</p><p class="fw-bold">{{ $expense->branch->name ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Cost Center</p><p class="fw-bold">{{ $expense->costCenter->name ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Project</p><p class="fw-bold">{{ $expense->project->name ?? '-' }}</p></div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Attachment</p>
                @if($expense->attachment)
                    <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-paperclip"></i> View Attachment</a>
                @else
                    <p class="fw-bold">-</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body d-flex gap-2">
        @if($expense->status === 'Pending')
        <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve</button>
        </form>
        @endif
        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
