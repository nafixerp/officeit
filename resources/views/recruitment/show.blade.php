@extends('layouts.app')
@section('title', 'Vacancy Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Vacancy Details</h1>
    <div>
        <a href="{{ route('recruitment.edit', $vacancy) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('recruitment.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h4>{{ $vacancy->title }}</h4>
                <p class="mb-1"><strong>Department:</strong> {{ $vacancy->department->name ?? '-' }}</p>
                <p class="mb-1"><strong>Positions:</strong> {{ $vacancy->positions }}</p>
                <p class="mb-1"><strong>Posted:</strong> {{ $vacancy->posted_date?->format('d M Y') ?? '-' }}</p>
                <p class="mb-1"><strong>Closing:</strong> {{ $vacancy->closing_date?->format('d M Y') ?? '-' }}</p>
            </div>
            <div class="col-md-4 text-end">
                @php $statusColors = ['open' => 'success', 'closed' => 'danger', 'on_hold' => 'warning', 'draft' => 'secondary']; @endphp
                <span class="badge bg-{{ $statusColors[$vacancy->status] ?? 'secondary' }} fs-6">{{ ucfirst(str_replace('_', ' ', $vacancy->status)) }}</span>
            </div>
        </div>
        @if($vacancy->description)
        <hr>
        <h6>Description</h6>
        <p>{{ $vacancy->description }}</p>
        @endif
        @if($vacancy->requirements)
        <h6>Requirements</h6>
        <p>{{ $vacancy->requirements }}</p>
        @endif
    </div>
</div>

<!-- Candidates List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Candidates ({{ count($vacancy->candidates ?? []) }})</h5>
        <a href="{{ route('candidates.create', ['recruitment_id' => $vacancy->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Candidate</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Interview Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacancy->candidates ?? [] as $candidate)
                <tr>
                    <td>{{ $candidate->name }}</td>
                    <td>{{ $candidate->email }}</td>
                    <td>{{ $candidate->phone }}</td>
                    <td>
                        @php $candColors = ['applied' => 'info', 'shortlisted' => 'primary', 'interview' => 'warning', 'offered' => 'success', 'hired' => 'success', 'rejected' => 'danger']; @endphp
                        <span class="badge bg-{{ $candColors[$candidate->status] ?? 'secondary' }}">{{ ucfirst($candidate->status) }}</span>
                    </td>
                    <td>{{ $candidate->interview_date?->format('d M Y H:i') ?? '-' }}</td>
                    <td>
                        <a href="{{ route('candidates.show', $candidate) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('candidates.edit', $candidate) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No candidates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
