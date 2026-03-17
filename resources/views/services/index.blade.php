@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Services</h1>
    <a href="{{ route('services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Billing Type</th>
                        <th class="text-end">Rate</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->code }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->category ?? '-' }}</td>
                            <td>{{ $service->billing_type ?? '-' }}</td>
                            <td class="text-end">{{ number_format($service->rate ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('services.edit', $service) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No services found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $services->links() }}
    </div>
</div>
@endsection
