@extends('layouts.app')

@section('title', 'Designations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Designations</h1>
    <a href="{{ route('designations.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($designations as $designation)
                        <tr>
                            <td>{{ $designation->name }}</td>
                            <td>{{ $designation->code }}</td>
                            <td>
                                <span class="badge bg-{{ $designation->is_active ? 'success' : 'secondary' }}">{{ $designation->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('designations.edit', $designation) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('designations.destroy', $designation) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No designations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $designations->links() }}
    </div>
</div>
@endsection
