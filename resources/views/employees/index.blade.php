@extends('layouts.app')
@section('title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Employees</h1>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add Employee
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="search" class="form-control" placeholder="Search name/ID..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="designation_id" class="form-select">
                    <option value="">All Designations</option>
                    @foreach($designations ?? [] as $designation)
                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    <option value="resigned" {{ request('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Employees Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees ?? [] as $employee)
                <tr>
                    <td>{{ $employee->employee_id_number }}</td>
                    <td>
                        @if($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="" class="avatar-sm" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                        @else
                            <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle" style="width:36px;height:36px;">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $employee->full_name }}</td>
                    <td>{{ $employee->department->name ?? '-' }}</td>
                    <td>{{ $employee->designation->name ?? '-' }}</td>
                    <td>{{ $employee->branch->name ?? '-' }}</td>
                    <td>
                        @php
                            $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'terminated' => 'danger', 'resigned' => 'warning'];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$employee->status] ?? 'secondary' }}">{{ ucfirst($employee->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($employees ?? collect(), 'links'))
    <div class="card-footer">{{ $employees->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
