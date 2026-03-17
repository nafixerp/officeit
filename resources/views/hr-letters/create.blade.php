@extends('layouts.app')
@section('title', 'Create HR Letter')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Create HR Letter</h1>
    <a href="{{ route('hr-letters.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('hr-letters.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="recipient_type" class="form-label">Recipient Type <span class="text-danger">*</span></label>
                    <select name="recipient_type" id="recipient_type" class="form-select @error('recipient_type') is-invalid @enderror" required>
                        <option value="employee" {{ old('recipient_type') == 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="candidate" {{ old('recipient_type') == 'candidate' ? 'selected' : '' }}>Candidate</option>
                    </select>
                    @error('recipient_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4" id="employeeSelectDiv">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror">
                        <option value="">Select Employee</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4" id="candidateSelectDiv" style="display:none;">
                    <label for="candidate_id" class="form-label">Candidate</label>
                    <select name="candidate_id" id="candidate_id" class="form-select @error('candidate_id') is-invalid @enderror">
                        <option value="">Select Candidate</option>
                        @foreach($candidates ?? [] as $candidate)
                            <option value="{{ $candidate->id }}" {{ old('candidate_id') == $candidate->id ? 'selected' : '' }}>{{ $candidate->name }}</option>
                        @endforeach
                    </select>
                    @error('candidate_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="letter_type" class="form-label">Letter Type <span class="text-danger">*</span></label>
                    <select name="letter_type" id="letter_type" class="form-select @error('letter_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="offer_letter" {{ old('letter_type') == 'offer_letter' ? 'selected' : '' }}>Offer Letter</option>
                        <option value="appointment_letter" {{ old('letter_type') == 'appointment_letter' ? 'selected' : '' }}>Appointment Letter</option>
                        <option value="experience_letter" {{ old('letter_type') == 'experience_letter' ? 'selected' : '' }}>Experience Letter</option>
                        <option value="salary_certificate" {{ old('letter_type') == 'salary_certificate' ? 'selected' : '' }}>Salary Certificate</option>
                        <option value="warning_letter" {{ old('letter_type') == 'warning_letter' ? 'selected' : '' }}>Warning Letter</option>
                        <option value="termination_letter" {{ old('letter_type') == 'termination_letter' ? 'selected' : '' }}>Termination Letter</option>
                        <option value="promotion_letter" {{ old('letter_type') == 'promotion_letter' ? 'selected' : '' }}>Promotion Letter</option>
                        <option value="noc" {{ old('letter_type') == 'noc' ? 'selected' : '' }}>NOC (No Objection Certificate)</option>
                        <option value="other" {{ old('letter_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('letter_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="content" class="form-label">Letter Content <span class="text-danger">*</span></label>
                    <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" rows="15" required>{{ old('content') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Letter</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('recipient_type').addEventListener('change', function() {
    const empDiv = document.getElementById('employeeSelectDiv');
    const candDiv = document.getElementById('candidateSelectDiv');
    if (this.value === 'candidate') {
        empDiv.style.display = 'none';
        candDiv.style.display = 'block';
    } else {
        empDiv.style.display = 'block';
        candDiv.style.display = 'none';
    }
});
</script>
@endpush
