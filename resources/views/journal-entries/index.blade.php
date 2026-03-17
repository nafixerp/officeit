@extends('layouts.app')
@section('title', 'Journal Entries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Journal Entries</h1>
    <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Journal Entry
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('journal-entries.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    @foreach(['General','Adjusting','Closing','Reversing','Opening'] as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['Draft','Posted','Cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Journal #</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Narration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalEntries ?? [] as $entry)
                <tr>
                    <td>{{ $entry->entry_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry->date)->format('d M Y') }}</td>
                    <td><span class="badge bg-info">{{ $entry->type }}</span></td>
                    <td class="text-end">{{ number_format($entry->total_amount, 2) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($entry->narration, 40) }}</td>
                    <td>
                        @php
                            $badgeClass = match($entry->status) {
                                'Draft' => 'bg-secondary',
                                'Posted' => 'bg-success',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $entry->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('journal-entries.show', $entry) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('journal-entries.edit', $entry) }}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            <form action="{{ route('journal-entries.destroy', $entry) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this journal entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No journal entries found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($journalEntries ?? collect(), 'links'))
    <div class="card-footer">{{ $journalEntries->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
