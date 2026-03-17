@extends('layouts.app')
@section('title', 'Contra Entries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Contra Entries</h1>
    <a href="{{ route('contra-entries.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Contra Entry</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Contra #</th><th>Date</th><th>From Account</th><th>To Account</th>
                    <th class="text-end">Amount</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contraEntries ?? [] as $entry)
                <tr>
                    <td>{{ $entry->entry_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
                    <td>{{ $entry->fromAccount->name ?? '-' }}</td>
                    <td>{{ $entry->toAccount->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($entry->amount, 2) }}</td>
                    <td><span class="badge {{ $entry->status == 'Posted' ? 'bg-success' : 'bg-secondary' }}">{{ $entry->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('contra-entries.show', $entry) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('contra-entries.edit', $entry) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('contra-entries.destroy', $entry) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No contra entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($contraEntries ?? collect(), 'links'))
    <div class="card-footer">{{ $contraEntries->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
