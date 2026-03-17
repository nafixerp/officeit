@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Project: {{ $project->name }}</h1>
    <div>
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('projects.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-semibold">Name</label>
                        <p class="mb-0">{{ $project->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-semibold">Code</label>
                        <p class="mb-0">{{ $project->code ?? '-' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted fw-semibold">Description</label>
                        <p class="mb-0">{{ $project->description ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-semibold">Cost Center</label>
                        <p class="mb-0">{{ $project->costCenter->name ?? '-' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-semibold">Budget</label>
                        <p class="mb-0">{{ number_format($project->budget ?? 0, 2) }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-semibold">Start Date</label>
                        <p class="mb-0">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-semibold">End Date</label>
                        <p class="mb-0">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-semibold">Status</label>
                        <p class="mb-0">
                            @php
                                $statusColors = [
                                    'PLANNED' => 'secondary',
                                    'IN_PROGRESS' => 'primary',
                                    'ON_HOLD' => 'warning',
                                    'COMPLETED' => 'success',
                                    'CANCELLED' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">{{ str_replace('_', ' ', $project->status ?? 'N/A') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Created</span>
                        <span>{{ $project->created_at ? $project->created_at->format('d M Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Last Updated</span>
                        <span>{{ $project->updated_at ? $project->updated_at->format('d M Y') : '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Budget</span>
                        <span class="fw-bold">{{ number_format($project->budget ?? 0, 2) }}</span>
                    </li>
                    @if($project->start_date && $project->end_date)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Duration</span>
                        <span>{{ \Carbon\Carbon::parse($project->start_date)->diffInDays(\Carbon\Carbon::parse($project->end_date)) }} days</span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
