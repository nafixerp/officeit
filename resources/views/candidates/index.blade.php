@extends('layouts.app')
@section('title', 'Candidates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Candidates</h1>
    <a href="{{ route('candidates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Candidate</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Vacancy</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Interview Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($candidates ?? [] as $candidate)
                <tr>
                    <td>{{ $candidate->name }}</td>
                    <td>{{ $candidate->recruitment->title ?? '-' }}</td>
                    <td>{{ $candidate->email }}</td>
                    <td>{{ $candidate->phone }}</td>
                    <td>
                        @php $candColors = ['applied' => 'info', 'shortlisted' => 'primary', 'interview' => 'warning', 'offered' => 'success', 'hired' => 'success', 'rejected' => 'danger']; @endphp
                        <span class="badge bg-{{ $candColors[$candidate->status] ?? 'secondary' }}">{{ ucfirst($candidate->status) }}</span>
                    </td>
                    <td>{{ $candidate->interview_date?->format('d M Y H:i') ?? '-' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('candidates.show', $candidate) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('candidates.edit', $candidate) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('candidates.destroy', $candidate) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No candidates found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($candidates ?? collect(), 'links'))
    <div class="card-footer">{{ $candidates->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
