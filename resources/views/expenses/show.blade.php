@extends('layouts.app')
@section('title', 'Expense Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Expense Details</h1>
    <div class="btn-group">
        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Expense Information</h5>
        @php
            $badgeClass = match($expense->status) {
                'Draft' => 'bg-secondary', 'Approved' => 'bg-info',
                'Paid' => 'bg-success', 'Cancelled' => 'bg-danger', default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $expense->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Category</p>
                <p class="fw-bold">{{ $expense->category }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted">Description</p>
                <p class="fw-bold">{{ $expense->description }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Amount</p>
                <p class="fw-bold fs-5">{{ number_format($expense->amount, 2) }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Tax Amount</p>
                <p class="fw-bold">{{ number_format($expense->tax_amount ?? 0, 2) }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Total</p>
                <p class="fw-bold fs-5 text-danger">{{ number_format(($expense->amount ?? 0) + ($expense->tax_amount ?? 0), 2) }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Payment Mode</p>
                <p class="fw-bold">{{ $expense->payment_mode }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Bank Account</p>
                <p class="fw-bold">{{ $expense->bankAccount->account_name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Branch</p>
                <p class="fw-bold">{{ $expense->branch->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Cost Center</p>
                <p class="fw-bold">{{ $expense->costCenter->name ?? '-' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Project</p>
                <p class="fw-bold">{{ $expense->project->name ?? '-' }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Recurring</p>
                <p class="fw-bold">{{ $expense->is_recurring ? 'Yes' : 'No' }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Attachment</p>
                @if($expense->attachment)
                    <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i> View File</a>
                @else
                    <p class="fw-bold">-</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
