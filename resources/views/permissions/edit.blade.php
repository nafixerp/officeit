@extends('layouts.app')

@section('title', 'Edit Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Edit Permissions: {{ str_replace('_', ' ', $role) }}</h1>
    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Back to Matrix</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('permissions.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="role" value="{{ $role }}">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Module</th>
                            <th class="text-center">View</th>
                            <th class="text-center">Create</th>
                            <th class="text-center">Edit</th>
                            <th class="text-center">Delete</th>
                            <th class="text-center">Approve</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $modules = $modules ?? ['Sales', 'Purchase', 'Finance', 'Inventory', 'HR', 'Payroll', 'Reports', 'Settings', 'Masters'];
                            $actions = ['view', 'create', 'edit', 'delete', 'approve'];
                        @endphp
                        @foreach($modules as $module)
                            <tr>
                                <td class="fw-semibold">{{ $module }}</td>
                                @foreach($actions as $action)
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[{{ $module }}][{{ $action }}]"
                                               value="1"
                                               {{ isset($permissions[$module][$action]) && $permissions[$module][$action] ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Permissions</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
