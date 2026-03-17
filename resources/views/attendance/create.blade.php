@extends('layouts.app')
@section('title', 'Mark Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Mark Bulk Attendance</h1>
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('attendance.store') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Overtime (hrs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees ?? [] as $index => $employee)
                    <tr>
                        <td>
                            <input type="hidden" name="attendance[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                            <strong>{{ $employee->full_name }}</strong>
                            <br><small class="text-muted">{{ $employee->employee_id_number }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="attendance[{{ $index }}][status]" id="present_{{ $index }}" value="present" checked>
                                <label class="btn btn-outline-success" for="present_{{ $index }}">Present</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $index }}][status]" id="absent_{{ $index }}" value="absent">
                                <label class="btn btn-outline-danger" for="absent_{{ $index }}">Absent</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $index }}][status]" id="half_{{ $index }}" value="half_day">
                                <label class="btn btn-outline-warning" for="half_{{ $index }}">Half Day</label>

                                <input type="radio" class="btn-check" name="attendance[{{ $index }}][status]" id="leave_{{ $index }}" value="leave">
                                <label class="btn btn-outline-info" for="leave_{{ $index }}">Leave</label>
                            </div>
                        </td>
                        <td>
                            <input type="time" name="attendance[{{ $index }}][check_in]" class="form-control form-control-sm" value="{{ old("attendance.$index.check_in", '09:00') }}">
                        </td>
                        <td>
                            <input type="time" name="attendance[{{ $index }}][check_out]" class="form-control form-control-sm" value="{{ old("attendance.$index.check_out", '18:00') }}">
                        </td>
                        <td>
                            <input type="number" step="0.5" name="attendance[{{ $index }}][overtime_hours]" class="form-control form-control-sm" value="{{ old("attendance.$index.overtime_hours", '0') }}" min="0">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Attendance</button>
    </div>
</form>
@endsection
