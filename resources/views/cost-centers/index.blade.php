@extends('layouts.app')

@section('title', 'Cost Centers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Cost Centers</h1>
    <a href="{{ route('cost-centers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($costCenters as $costCenter)
                        <tr>
                            <td>{{ $costCenter->name }}</td>
                            <td>{{ $costCenter->code ?? '-' }}</td>
                            <td>{{ $costCenter->type ?? '-' }}</td>
                            <td>{{ $costCenter->branch->name ?? '-' }}</td>
                            <td>{{ $costCenter->department->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('cost-centers.edit', $costCenter) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('cost-centers.destroy', $costCenter) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No cost centers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $costCenters->links() }}
    </div>
</div>
@endsection
