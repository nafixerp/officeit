@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Projects</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Cost Center</th>
                        <th>Start</th>
                        <th>End</th>
                        <th class="text-end">Budget</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->code ?? '-' }}</td>
                            <td>{{ $project->costCenter->name ?? '-' }}</td>
                            <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}</td>
                            <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</td>
                            <td class="text-end">{{ number_format($project->budget ?? 0, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'PLANNED' => 'secondary',
                                        'IN_PROGRESS' => 'primary',
                                        'ON_HOLD' => 'warning',
                                        'COMPLETED' => 'success',
                                        'CANCELLED' => 'danger',
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">{{ $project->status ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No projects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $projects->links() }}
    </div>
</div>
@endsection
