@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Profile</h1>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit Profile</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                @if($user->avatar ?? false)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;" class="mb-3">
                @else
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width:120px;height:120px;font-size:3rem;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <h4>{{ $user->name }}</h4>
                <p class="text-muted mb-1">{{ $user->email }}</p>
                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'user')) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Profile Details</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:30%">Name</td><td>{{ $user->name }}</td></tr>
                    <tr><td class="text-muted">Email</td><td>{{ $user->email }}</td></tr>
                    <tr><td class="text-muted">Role</td><td>{{ ucfirst(str_replace('_', ' ', $user->role ?? '-')) }}</td></tr>
                    <tr><td class="text-muted">Branch</td><td>{{ $user->branch->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Department</td><td>{{ $user->department->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>
                        @if($user->is_active ?? true)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td></tr>
                    <tr><td class="text-muted">Member Since</td><td>{{ $user->created_at?->format('d M Y') ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Last Login</td><td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
