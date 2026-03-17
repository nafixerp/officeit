@extends('layouts.app')
@section('title', 'Employee Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Employee Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="fas fa-print"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/employee-report') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-filter"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Branch</th>
                    <th>Status</th>
                    <th>Phone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees ?? [] as $employee)
                <tr>
                    <td>{{ $employee->employee_id ?? $employee->id }}</td>
                    <td>{{ $employee->name ?? ($employee->first_name . ' ' . $employee->last_name) }}</td>
                    <td>{{ $employee->department->name ?? '-' }}</td>
                    <td>{{ $employee->designation->name ?? $employee->designation ?? '-' }}</td>
                    <td>{{ $employee->branch->name ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ ($employee->is_active ?? $employee->status == 'active') ? 'success' : 'secondary' }}">
                            {{ $employee->is_active ? 'Active' : ($employee->status ?? 'Inactive') }}
                        </span>
                    </td>
                    <td>{{ $employee->phone ?? '-' }}</td>
                    <td>{{ $employee->email ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
