@extends('layouts.app')
@section('title', 'Recruitment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Recruitment - Vacancies</h1>
    <a href="{{ route('recruitment.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Vacancy</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Vacancy</th>
                    <th>Department</th>
                    <th>Positions</th>
                    <th>Status</th>
                    <th>Posted Date</th>
                    <th>Closing Date</th>
                    <th>Candidates</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacancies ?? [] as $vacancy)
                <tr>
                    <td>{{ $vacancy->title }}</td>
                    <td>{{ $vacancy->department->name ?? '-' }}</td>
                    <td>{{ $vacancy->positions }}</td>
                    <td>
                        @php $statusColors = ['open' => 'success', 'closed' => 'danger', 'on_hold' => 'warning', 'draft' => 'secondary']; @endphp
                        <span class="badge bg-{{ $statusColors[$vacancy->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $vacancy->status)) }}</span>
                    </td>
                    <td>{{ $vacancy->posted_date?->format('d M Y') }}</td>
                    <td>{{ $vacancy->closing_date?->format('d M Y') }}</td>
                    <td><span class="badge bg-info">{{ $vacancy->candidates_count ?? $vacancy->candidates->count() ?? 0 }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('recruitment.show', $vacancy) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('recruitment.edit', $vacancy) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('recruitment.destroy', $vacancy) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No vacancies found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($vacancies ?? collect(), 'links'))
    <div class="card-footer">{{ $vacancies->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
