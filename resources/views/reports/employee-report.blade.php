@extends('layouts.app')
@section('title', 'Employee Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Employee Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/employees') }}" class="row g-3">
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
                <label class="form-label">Designation</label>
                <select name="designation_id" class="form-select">
                    <option value="">All Designations</option>
                    @foreach($designations ?? [] as $designation)
                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Branch</th>
                    <th>Joining Date</th>
                    <th>Mobile</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees ?? [] as $employee)
                <tr>
                    <td>{{ $employee->employee_id_number }}</td>
                    <td>{{ $employee->full_name }}</td>
                    <td>{{ $employee->department->name ?? '-' }}</td>
                    <td>{{ $employee->designation->name ?? '-' }}</td>
                    <td>{{ $employee->branch->name ?? '-' }}</td>
                    <td>{{ $employee->joining_date?->format('d M Y') }}</td>
                    <td>{{ $employee->mobile ?? '-' }}</td>
                    <td>
                        @php $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'terminated' => 'danger', 'resigned' => 'warning']; @endphp
                        <span class="badge bg-{{ $statusColors[$employee->status] ?? 'secondary' }}">{{ ucfirst($employee->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No employees found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
