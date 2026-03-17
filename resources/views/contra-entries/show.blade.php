@extends('layouts.app')
@section('title', 'Contra Entry #' . $contraEntry->entry_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Contra Entry #{{ $contraEntry->entry_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('contra-entries.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('contra-entries.edit', $contraEntry) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Entry Details</h5>
        <span class="badge {{ $contraEntry->status == 'Posted' ? 'bg-success' : 'bg-secondary' }} fs-6">{{ $contraEntry->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($contraEntry->date)->format('d M Y') }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">From Account</p><p class="fw-bold">{{ $contraEntry->fromAccount->name ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">To Account</p><p class="fw-bold">{{ $contraEntry->toAccount->name ?? '-' }}</p></div>
            <div class="col-md-3"><p class="mb-1 text-muted">Amount</p><p class="fw-bold fs-5">{{ number_format($contraEntry->amount, 2) }}</p></div>
        </div>
        @if($contraEntry->narration)
        <div class="row mt-3"><div class="col-12"><p class="mb-1 text-muted">Narration</p><p>{{ $contraEntry->narration }}</p></div></div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body d-flex gap-2">
        @if($contraEntry->status === 'Draft')
        <form action="{{ route('contra-entries.post', $contraEntry) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Post Entry</button>
        </form>
        @endif
        <form action="{{ route('contra-entries.destroy', $contraEntry) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>
@endsection
