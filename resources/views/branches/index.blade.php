@extends('layouts.app')

@section('title', 'Branches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Branches</h1>
    <a href="{{ route('branches.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Country</th>
                        <th>Manager</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->code }}</td>
                            <td>{{ $branch->country->name ?? '-' }}</td>
                            <td>{{ $branch->manager_name ?? '-' }}</td>
                            <td>{{ $branch->phone ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $branch->is_active ? 'success' : 'secondary' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('branches.edit', $branch) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No branches found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $branches->links() }}
    </div>
</div>
@endsection
