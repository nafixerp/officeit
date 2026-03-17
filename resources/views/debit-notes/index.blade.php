@extends('layouts.app')
@section('title', 'Debit Notes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Debit Notes</h1>
    <a href="{{ route('debit-notes.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Debit Note</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Debit Note #</th><th>Date</th><th>Supplier</th><th>Purchase Invoice #</th>
                    <th class="text-end">Amount</th><th class="text-end">Tax</th><th>Reason</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($debitNotes ?? [] as $note)
                <tr>
                    <td>{{ $note->debit_note_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($note->date)->format('d M Y') }}</td>
                    <td>{{ $note->supplier->name ?? '-' }}</td>
                    <td>{{ $note->invoice->invoice_number ?? '-' }}</td>
                    <td class="text-end">{{ number_format($note->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($note->tax_amount, 2) }}</td>
                    <td>{{ Str::limit($note->reason, 30) }}</td>
                    <td><span class="badge {{ $note->status == 'Applied' ? 'bg-success' : ($note->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $note->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('debit-notes.show', $note) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('debit-notes.edit', $note) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('debit-notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No debit notes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($debitNotes ?? collect(), 'links'))
    <div class="card-footer">{{ $debitNotes->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
