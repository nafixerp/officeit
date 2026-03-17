@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Attendance</h1>
    <div>
        <a href="{{ route('attendance.monthly') }}" class="btn btn-info"><i class="bi bi-calendar-month"></i> Monthly View</a>
        <a href="{{ route('attendance.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Mark Attendance</a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ request('date') }}" placeholder="Date">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>On Leave</option>
                </select>
            </div>
            <div class="col-md-3">
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
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Overtime (hrs)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances ?? [] as $attendance)
                <tr>
                    <td>{{ $attendance->date?->format('d M Y') }}</td>
                    <td>{{ $attendance->employee->full_name ?? '-' }}</td>
                    <td>{{ $attendance->check_in ?? '-' }}</td>
                    <td>{{ $attendance->check_out ?? '-' }}</td>
                    <td>
                        @php $attColors = ['present' => 'success', 'absent' => 'danger', 'half_day' => 'warning', 'leave' => 'info']; @endphp
                        <span class="badge bg-{{ $attColors[$attendance->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                    </td>
                    <td>{{ $attendance->overtime_hours ?? 0 }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($attendances ?? collect(), 'links'))
    <div class="card-footer">{{ $attendances->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
