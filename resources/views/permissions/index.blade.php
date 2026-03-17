@extends('layouts.app')
@section('title', 'Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Permissions Matrix</h1>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Module</th>
                    @foreach($roles ?? ['admin', 'manager', 'accountant', 'hr_manager', 'sales_manager', 'purchase_manager', 'warehouse_manager', 'employee', 'viewer'] as $role)
                        <th class="text-center">
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                            <br>
                            <a href="{{ route('permissions.edit', $role) }}" class="btn btn-sm btn-outline-primary mt-1"><i class="bi bi-pencil"></i></a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($modules ?? ['Sales', 'Purchase', 'Accounting', 'HR', 'Payroll', 'Inventory', 'Reports', 'Settings', 'Users'] as $module)
                <tr>
                    <td class="fw-bold">{{ $module }}</td>
                    @foreach($roles ?? ['admin', 'manager', 'accountant', 'hr_manager', 'sales_manager', 'purchase_manager', 'warehouse_manager', 'employee', 'viewer'] as $role)
                        <td class="text-center">
                            @php
                                $perms = $permissions[$role][$module] ?? [];
                                $hasView = in_array('view', $perms);
                                $hasCreate = in_array('create', $perms);
                                $hasEdit = in_array('edit', $perms);
                                $hasDelete = in_array('delete', $perms);
                            @endphp
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                <span class="badge {{ $hasView ? 'bg-success' : 'bg-light text-muted' }}" title="View">V</span>
                                <span class="badge {{ $hasCreate ? 'bg-primary' : 'bg-light text-muted' }}" title="Create">C</span>
                                <span class="badge {{ $hasEdit ? 'bg-warning' : 'bg-light text-muted' }}" title="Edit">E</span>
                                <span class="badge {{ $hasDelete ? 'bg-danger' : 'bg-light text-muted' }}" title="Delete">D</span>
                            </div>
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <small class="text-muted">
            <span class="badge bg-success">V</span> View
            <span class="badge bg-primary ms-2">C</span> Create
            <span class="badge bg-warning ms-2">E</span> Edit
            <span class="badge bg-danger ms-2">D</span> Delete
        </small>
    </div>
</div>
@endsection
