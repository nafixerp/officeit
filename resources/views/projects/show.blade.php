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
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Project Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2"><strong>Name:</strong><br>{{ $project->name }}</div>
                    <div class="col-md-6 mb-2"><strong>Code:</strong><br>{{ $project->code }}</div>
                    <div class="col-md-6 mb-2"><strong>Cost Center:</strong><br>{{ $project->costCenter->name ?? '-' }}</div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong><br>
                        @php
                            $statusColors = ['ACTIVE' => 'success', 'COMPLETED' => 'primary', 'ON_HOLD' => 'warning', 'CANCELLED' => 'danger'];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">{{ $project->status ?? '-' }}</span>
                    </div>
                    <div class="col-md-4 mb-2"><strong>Start Date:</strong><br>{{ $project->start_date?->format('Y-m-d') ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>End Date:</strong><br>{{ $project->end_date?->format('Y-m-d') ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Budget:</strong><br>{{ number_format($project->budget ?? 0, 2) }}</div>
                    <div class="col-12 mb-2"><strong>Description:</strong><br>{{ $project->description ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Financial Summary</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Budget:</strong> {{ number_format($project->budget ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Total Income:</strong> {{ number_format($totalIncome ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Total Expense:</strong> {{ number_format($totalExpense ?? 0, 2) }}</div>
                <hr>
                <div class="mb-2"><strong>Remaining Budget:</strong> {{ number_format(($project->budget ?? 0) - ($totalExpense ?? 0), 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
