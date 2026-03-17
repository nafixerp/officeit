@extends('layouts.app')

@section('title', 'Countries')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Countries</h1>
    <a href="{{ route('countries.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>ISO Code</th>
                        <th>Phone Code</th>
                        <th>Tax Type</th>
                        <th>Tax %</th>
                        <th>Currency</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($countries as $country)
                        <tr>
                            <td>{{ $country->name }}</td>
                            <td>{{ $country->iso_code }}</td>
                            <td>{{ $country->phone_code }}</td>
                            <td>{{ $country->tax_type ?? '-' }}</td>
                            <td>{{ $country->tax_percentage ?? '-' }}</td>
                            <td>{{ $country->currency->code ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $country->is_active ? 'success' : 'secondary' }}">{{ $country->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('countries.edit', $country) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('countries.destroy', $country) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No countries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $countries->links() }}
    </div>
</div>
@endsection
