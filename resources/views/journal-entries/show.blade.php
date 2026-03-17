@extends('layouts.app')
@section('title', 'Journal Entry #' . $journalEntry->entry_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Journal Entry #{{ $journalEntry->entry_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('journal-entries.edit', $journalEntry) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Entry Details</h5>
        @php
            $badgeClass = match($journalEntry->status) {
                'Draft' => 'bg-secondary', 'Posted' => 'bg-success',
                'Cancelled' => 'bg-danger', default => 'bg-secondary',
            };
        @endphp
        <span class="badge {{ $badgeClass }} fs-6">{{ $journalEntry->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Entry Number</p>
                <p class="fw-bold">{{ $journalEntry->entry_number }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($journalEntry->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Type</p>
                <p class="fw-bold">{{ $journalEntry->type }}</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Currency</p>
                <p class="fw-bold">{{ $journalEntry->currency ?? 'USD' }}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Reference</p>
                <p class="fw-bold">{{ $journalEntry->reference_number ?? '-' }}</p>
            </div>
            <div class="col-md-9">
                <p class="mb-1 text-muted">Narration</p>
                <p class="fw-bold">{{ $journalEntry->narration ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Lines Table --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Journal Lines</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Account</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th>Narration</th>
                </tr>
            </thead>
            <tbody>
                @php $totalDebit = 0; $totalCredit = 0; @endphp
                @foreach($journalEntry->lines as $idx => $line)
                @php $totalDebit += $line->debit; $totalCredit += $line->credit; @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $line->account->code ?? '' }} - {{ $line->account->name ?? '-' }}</td>
                    <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                    <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                    <td>{{ $line->narration ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-warning">
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">Totals:</td>
                    <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
