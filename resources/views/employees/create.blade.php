@extends('layouts.app')
@section('title', 'Add Employee')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Personal Information -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Personal Information</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="employee_id_number" class="form-label">Employee ID <span class="text-danger">*</span></label>
                        <input type="text" name="employee_id_number" id="employee_id_number" class="form-control @error('employee_id_number') is-invalid @enderror" value="{{ old('employee_id_number') }}" required>
                        @error('employee_id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="nationality" class="form-label">Nationality</label>
                        <input type="text" name="nationality" id="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality') }}">
                        @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="passport_number" class="form-label">Passport Number</label>
                        <input type="text" name="passport_number" id="passport_number" class="form-control @error('passport_number') is-invalid @enderror" value="{{ old('passport_number') }}">
                        @error('passport_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="national_id" class="form-label">National ID</label>
                        <input type="text" name="national_id" id="national_id" class="form-control @error('national_id') is-invalid @enderror" value="{{ old('national_id') }}">
                        @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="visa_details" class="form-label">Visa Details</label>
                        <textarea name="visa_details" id="visa_details" class="form-control @error('visa_details') is-invalid @enderror" rows="3">{{ old('visa_details') }}</textarea>
                        @error('visa_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="text" name="mobile" id="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}">
                        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Address Row -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="country_id" class="form-label">Country</label>
                        <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror">
                            <option value="">Select Country</option>
                            @foreach($countries ?? [] as $country)
                                <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" name="joining_date" id="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date') }}" required>
                        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department / Designation / Branch -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Position Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="designation_id" class="form-label">Designation <span class="text-danger">*</span></label>
                    <select name="designation_id" id="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                        <option value="">Select Designation</option>
                        @foreach($designations ?? [] as $designation)
                            <option value="{{ $designation->id }}" {{ old('designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                        @endforeach
                    </select>
                    @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                        <option value="">Select Branch</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Salary & Bank Details -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Salary & Bank Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="basic_salary" class="form-label">Basic Salary <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="basic_salary" id="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror" value="{{ old('basic_salary') }}" required>
                    @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name') }}">
                    @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="bank_account_number" class="form-label">Bank Account Number</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number') }}">
                    @error('bank_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="bank_iban" class="form-label">Bank IBAN</label>
                    <input type="text" name="bank_iban" id="bank_iban" class="form-control @error('bank_iban') is-invalid @enderror" value="{{ old('bank_iban') }}">
                    @error('bank_iban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Emergency Contact</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name') }}">
                    @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control @error('emergency_contact_phone') is-invalid @enderror" value="{{ old('emergency_contact_phone') }}">
                    @error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Employee</button>
    </div>
</form>
@endsection
