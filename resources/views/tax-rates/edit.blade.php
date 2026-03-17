@extends('layouts.app')

@section('title', 'Edit Tax Rate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Edit Tax Rate: {{ $taxRate->name }}</h1>
    <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('tax-rates.update', $taxRate) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tax_profile_id" class="form-label">Tax Profile <span class="text-danger">*</span></label>
                    <select class="form-select @error('tax_profile_id') is-invalid @enderror" id="tax_profile_id" name="tax_profile_id" required>
                        <option value="">Select Tax Profile</option>
                        @foreach($taxProfiles as $profile)
                            <option value="{{ $profile->id }}" {{ old('tax_profile_id', $taxRate->tax_profile_id) == $profile->id ? 'selected' : '' }}>{{ $profile->name }}</option>
                        @endforeach
                    </select>
                    @error('tax_profile_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $taxRate->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $taxRate->code) }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="rate" class="form-label">Rate % <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" class="form-control @error('rate') is-invalid @enderror" id="rate" name="rate" value="{{ old('rate', $taxRate->rate) }}" required>
                    @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                        <option value="">Select Type</option>
                        @foreach(['INCLUSIVE', 'EXCLUSIVE'] as $type)
                            <option value="{{ $type }}" {{ old('type', $taxRate->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $taxRate->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Default Rate</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $taxRate->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Tax Rate</button>
            <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
