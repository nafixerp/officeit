@extends('layouts.app')
@section('title', 'HR Letters')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">HR Letters</h1>
    <a href="{{ route('hr-letters.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create Letter</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Employee/Candidate</th>
                    <th>Letter Type</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters ?? [] as $index => $letter)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $letter->employee->full_name ?? $letter->candidate->name ?? '-' }}</td>
                    <td><span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $letter->letter_type)) }}</span></td>
                    <td>{{ $letter->created_at?->format('d M Y') }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('hr-letters.show', $letter) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('hr-letters.print', $letter) }}" class="btn btn-outline-primary" target="_blank"><i class="bi bi-printer"></i></a>
                            <form action="{{ route('hr-letters.destroy', $letter) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No HR letters found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($letters ?? collect(), 'links'))
    <div class="card-footer">{{ $letters->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
