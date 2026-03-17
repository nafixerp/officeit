@extends('layouts.app')
@section('title', 'Add Employee Loan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Employee Loan</h1>
    <a href="{{ route('employee-loans.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('employee-loans.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">Select Employee</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }} ({{ $employee->employee_id_number }})</option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="loan_type" class="form-label">Loan Type <span class="text-danger">*</span></label>
                    <input type="text" name="loan_type" id="loan_type" class="form-control @error('loan_type') is-invalid @enderror" value="{{ old('loan_type') }}" placeholder="e.g., Personal, Housing, Car" required>
                    @error('loan_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="amount" class="form-label">Total Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="monthly_deduction" class="form-label">Monthly Deduction <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="monthly_deduction" id="monthly_deduction" class="form-control @error('monthly_deduction') is-invalid @enderror" value="{{ old('monthly_deduction') }}" required>
                    @error('monthly_deduction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" required>
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Loan</button>
            </div>
        </form>
    </div>
</div>
@endsection
