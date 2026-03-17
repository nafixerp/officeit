@extends('layouts.app')
@section('title', 'Candidate Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Candidate Details</h1>
    <div>
        <a href="{{ route('candidates.edit', $candidate) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('candidates.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Candidate Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:30%">Name</td><td>{{ $candidate->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $candidate->email }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $candidate->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Vacancy</td><td>{{ $candidate->recruitment->title ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>
                        @php $candColors = ['applied' => 'info', 'shortlisted' => 'primary', 'interview' => 'warning', 'offered' => 'success', 'hired' => 'success', 'rejected' => 'danger']; @endphp
                        <span class="badge bg-{{ $candColors[$candidate->status] ?? 'secondary' }}">{{ ucfirst($candidate->status) }}</span>
                    </td></tr>
                    <tr><td class="text-muted">Interview Date</td><td>{{ $candidate->interview_date?->format('d M Y H:i') ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Offer Salary</td><td>{{ $candidate->offer_salary ? number_format($candidate->offer_salary, 2) : '-' }}</td></tr>
                    <tr><td class="text-muted">CV</td><td>
                        @if($candidate->cv)
                            <a href="{{ asset('storage/' . $candidate->cv) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download CV</a>
                        @else
                            -
                        @endif
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Evaluation Notes</h5></div>
            <div class="card-body">
                {{ $candidate->evaluation_notes ?? 'No evaluation notes.' }}
            </div>
        </div>
    </div>
</div>
@endsection
