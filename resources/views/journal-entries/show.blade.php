@extends('layouts.app')
@section('title', 'Journal Entry #' . $journalEntry->journal_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Journal Entry #{{ $journalEntry->journal_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">Back</a>
        <a href="{{ route('journal-entries.edit', $journalEntry) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Entry Details</h5>
        <span class="badge {{ $journalEntry->status == 'Posted' ? 'bg-success' : 'bg-secondary' }} fs-6">{{ $journalEntry->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2"><p class="mb-1 text-muted">Date</p><p class="fw-bold">{{ \Carbon\Carbon::parse($journalEntry->date)->format('d M Y') }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Type</p><p class="fw-bold">{{ $journalEntry->type }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Currency</p><p class="fw-bold">{{ $journalEntry->currency }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Branch</p><p class="fw-bold">{{ $journalEntry->branch->name ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Project</p><p class="fw-bold">{{ $journalEntry->project->name ?? '-' }}</p></div>
            <div class="col-md-2"><p class="mb-1 text-muted">Cost Center</p><p class="fw-bold">{{ $journalEntry->costCenter->name ?? '-' }}</p></div>
        </div>
        @if($journalEntry->narration)
        <div class="row mt-2"><div class="col-12"><p class="mb-1 text-muted">Narration</p><p>{{ $journalEntry->narration }}</p></div></div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Journal Lines</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th>Narration</th><th>Cost Center</th><th>Project</th></tr>
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
                    <td>{{ $line->narration ?? '' }}</td>
                    <td>{{ $line->costCenter->name ?? '-' }}</td>
                    <td>{{ $line->project->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="2" class="text-end">Totals:</td>
                    <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                    <td colspan="3">
                        @if(abs($totalDebit - $totalCredit) < 0.01)
                            <span class="text-success">Balanced</span>
                        @else
                            <span class="text-danger">Difference: {{ number_format(abs($totalDebit - $totalCredit), 2) }}</span>
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
