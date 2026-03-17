@extends('layouts.app')

@section('title', 'Edit Tax Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Edit Tax Profile: {{ $taxProfile->name }}</h1>
    <a href="{{ route('tax-profiles.index') }}" class="btn btn-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('tax-profiles.update', $taxProfile) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $taxProfile->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="country_id" class="form-label">Country <span class="text-danger">*</span></label>
                    <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id" required>
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ old('country_id', $taxProfile->country_id) == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                        @endforeach
                    </select>
                    @error('country_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tax_type" class="form-label">Tax Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('tax_type') is-invalid @enderror" id="tax_type" name="tax_type" required>
                        <option value="">Select Tax Type</option>
                        @foreach(['VAT', 'GST', 'SALES_TAX', 'NONE'] as $type)
                            <option value="{{ $type }}" {{ old('tax_type', $taxProfile->tax_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('tax_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $taxProfile->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Tax Profile</button>
            <a href="{{ route('tax-profiles.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
