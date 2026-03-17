@extends('layouts.app')
@section('title', 'Add Candidate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Candidate</h1>
    <a href="{{ route('candidates.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="recruitment_id" class="form-label">Vacancy <span class="text-danger">*</span></label>
                    <select name="recruitment_id" id="recruitment_id" class="form-select @error('recruitment_id') is-invalid @enderror" required>
                        <option value="">Select Vacancy</option>
                        @foreach($recruitments ?? [] as $recruitment)
                            <option value="{{ $recruitment->id }}" {{ old('recruitment_id', request('recruitment_id')) == $recruitment->id ? 'selected' : '' }}>{{ $recruitment->title }}</option>
                        @endforeach
                    </select>
                    @error('recruitment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="cv" class="form-label">CV / Resume</label>
                    <input type="file" name="cv" id="cv" class="form-control @error('cv') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    @error('cv')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="applied" {{ old('status') == 'applied' ? 'selected' : '' }}>Applied</option>
                        <option value="shortlisted" {{ old('status') == 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                        <option value="interview" {{ old('status') == 'interview' ? 'selected' : '' }}>Interview</option>
                        <option value="offered" {{ old('status') == 'offered' ? 'selected' : '' }}>Offered</option>
                        <option value="hired" {{ old('status') == 'hired' ? 'selected' : '' }}>Hired</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="interview_date" class="form-label">Interview Date</label>
                    <input type="datetime-local" name="interview_date" id="interview_date" class="form-control @error('interview_date') is-invalid @enderror" value="{{ old('interview_date') }}">
                    @error('interview_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="offer_salary" class="form-label">Offer Salary</label>
                    <input type="number" step="0.01" name="offer_salary" id="offer_salary" class="form-control @error('offer_salary') is-invalid @enderror" value="{{ old('offer_salary') }}">
                    @error('offer_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="evaluation_notes" class="form-label">Evaluation Notes</label>
                    <textarea name="evaluation_notes" id="evaluation_notes" class="form-control @error('evaluation_notes') is-invalid @enderror" rows="4">{{ old('evaluation_notes') }}</textarea>
                    @error('evaluation_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Candidate</button>
            </div>
        </form>
    </div>
</div>
@endsection
