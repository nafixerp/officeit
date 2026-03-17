@extends('layouts.app')
@section('title', 'Add Leave Type')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Leave Type</h1>
    <a href="{{ route('leave-types.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('leave-types.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="days_per_year" class="form-label">Days Per Year <span class="text-danger">*</span></label>
                    <input type="number" name="days_per_year" id="days_per_year" class="form-control @error('days_per_year') is-invalid @enderror" value="{{ old('days_per_year') }}" required>
                    @error('days_per_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_paid" id="is_paid" class="form-check-input" value="1" {{ old('is_paid') ? 'checked' : '' }}>
                        <label for="is_paid" class="form-check-label">Is Paid Leave</label>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
