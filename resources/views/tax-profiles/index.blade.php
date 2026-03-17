@extends('layouts.app')

@section('title', 'Tax Profiles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tax Profiles</h1>
    <a href="{{ route('tax-profiles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Tax Type</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxProfiles as $profile)
                        <tr>
                            <td>{{ $profile->name }}</td>
                            <td>{{ $profile->country->name ?? '-' }}</td>
                            <td>{{ $profile->tax_type ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $profile->is_active ? 'success' : 'secondary' }}">{{ $profile->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('tax-profiles.edit', $profile) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('tax-profiles.destroy', $profile) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No tax profiles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $taxProfiles->links() }}
    </div>
</div>
@endsection
