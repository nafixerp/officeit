@extends('layouts.app')
@section('title', 'Leave Types')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Leave Types</h1>
    <a href="{{ route('leave-types.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Leave Type</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Days Per Year</th>
                    <th>Paid</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes ?? [] as $index => $type)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $type->name }}</td>
                    <td><code>{{ $type->code }}</code></td>
                    <td>{{ $type->days_per_year }}</td>
                    <td>
                        @if($type->is_paid)
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-secondary">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('leave-types.edit', $type) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('leave-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No leave types found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
