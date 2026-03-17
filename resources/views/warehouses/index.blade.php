@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Warehouses</h1>
    <a href="{{ route('warehouses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Branch</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td>{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->code ?? '-' }}</td>
                            <td>{{ $warehouse->branch->name ?? '-' }}</td>
                            <td>{{ $warehouse->address ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }}">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No warehouses found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $warehouses->links() }}
    </div>
</div>
@endsection
