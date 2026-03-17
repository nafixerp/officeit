@extends('layouts.app')
@section('title', 'Generate Payroll')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Generate Payroll</h1>
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('payroll.store') }}" method="POST" id="payrollForm">
    @csrf

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                    <select name="month" id="month" class="form-select @error('month') is-invalid @enderror" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ old('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                    @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                    <select name="year" id="year" class="form-select @error('year') is-invalid @enderror" required>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-info w-100" id="generatePreview"><i class="bi bi-calculator"></i> Generate Preview</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Table -->
    <div class="card" id="previewCard" style="display:none;">
        <div class="card-header">
            <h5 class="mb-0">Payroll Preview</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Total Earnings</th>
                        <th class="text-end">Total Deductions</th>
                        <th class="text-end">Net Salary</th>
                    </tr>
                </thead>
                <tbody id="previewBody">
                    @foreach($employees ?? [] as $employee)
                    <tr>
                        <td>
                            <input type="hidden" name="employees[]" value="{{ $employee->id }}">
                            {{ $employee->full_name }}
                        </td>
                        <td class="text-end">{{ number_format($employee->basic_salary ?? 0, 2) }}</td>
                        <td class="text-end text-success">{{ number_format($employee->total_earnings ?? $employee->basic_salary ?? 0, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($employee->total_deductions ?? 0, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format(($employee->total_earnings ?? $employee->basic_salary ?? 0) - ($employee->total_deductions ?? 0), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td class="fw-bold">Grand Total</td>
                        <td class="text-end fw-bold">{{ number_format(collect($employees ?? [])->sum('basic_salary'), 2) }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format(collect($employees ?? [])->sum(fn($e) => $e->total_earnings ?? $e->basic_salary ?? 0), 2) }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format(collect($employees ?? [])->sum('total_deductions'), 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format(collect($employees ?? [])->sum(fn($e) => ($e->total_earnings ?? $e->basic_salary ?? 0) - ($e->total_deductions ?? 0)), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Confirm & Save Payroll</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('generatePreview').addEventListener('click', function() {
    document.getElementById('previewCard').style.display = 'block';
});
</script>
@endpush
