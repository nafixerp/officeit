@extends('layouts.app')
@section('title', 'Reconciliation Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Reconciliation Details</h1>
    <a href="{{ route('bank-reconciliations.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Bank Account</h6>
                <h5>{{ $reconciliation->bankAccount->name ?? $reconciliation->bankAccount->account_name ?? '-' }}</h5>
                <p class="text-muted mb-0">{{ $reconciliation->bankAccount->account_number ?? '' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted">Date</h6>
                <h5>{{ $reconciliation->date?->format('d M Y') }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <h6 class="text-muted">Statement Balance</h6>
                <h5>{{ number_format($reconciliation->statement_balance ?? 0, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info bg-opacity-10">
            <div class="card-body text-center">
                <h6 class="text-muted">Book Balance</h6>
                <h5>{{ number_format($reconciliation->book_balance ?? 0, 2) }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card {{ ($reconciliation->difference ?? 0) == 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10">
            <div class="card-body text-center">
                <h6 class="text-muted">Difference</h6>
                <h5>{{ number_format($reconciliation->difference ?? 0, 2) }}</h5>
            </div>
        </div>
    </div>
</div>

@if($reconciliation->notes)
<div class="card">
    <div class="card-header"><h5 class="mb-0">Notes</h5></div>
    <div class="card-body">{{ $reconciliation->notes }}</div>
</div>
@endif
@endsection
