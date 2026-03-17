@extends('layouts.app')
@section('title', 'Add Asset')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Asset</h1>
    <a href="{{ route('assets.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('assets.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="code" class="form-label">Asset Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category') }}" placeholder="e.g., Furniture, IT Equipment, Vehicle">
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="purchase_date" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date') }}" required>
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="purchase_value" class="form-label">Purchase Value <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="purchase_value" id="purchase_value" class="form-control @error('purchase_value') is-invalid @enderror" value="{{ old('purchase_value') }}" required>
                    @error('purchase_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="salvage_value" class="form-label">Salvage Value</label>
                    <input type="number" step="0.01" name="salvage_value" id="salvage_value" class="form-control @error('salvage_value') is-invalid @enderror" value="{{ old('salvage_value', 0) }}">
                    @error('salvage_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="depreciation_method" class="form-label">Depreciation Method</label>
                    <select name="depreciation_method" id="depreciation_method" class="form-select @error('depreciation_method') is-invalid @enderror">
                        <option value="straight_line" {{ old('depreciation_method') == 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                        <option value="declining_balance" {{ old('depreciation_method') == 'declining_balance' ? 'selected' : '' }}>Declining Balance</option>
                        <option value="units_of_production" {{ old('depreciation_method') == 'units_of_production' ? 'selected' : '' }}>Units of Production</option>
                    </select>
                    @error('depreciation_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="useful_life_years" class="form-label">Useful Life (Years)</label>
                    <input type="number" name="useful_life_years" id="useful_life_years" class="form-control @error('useful_life_years') is-invalid @enderror" value="{{ old('useful_life_years') }}">
                    @error('useful_life_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="depreciation_rate" class="form-label">Depreciation Rate (%)</label>
                    <input type="number" step="0.01" name="depreciation_rate" id="depreciation_rate" class="form-control @error('depreciation_rate') is-invalid @enderror" value="{{ old('depreciation_rate') }}">
                    @error('depreciation_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}">
                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select name="assigned_to" id="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                        <option value="">None</option>
                        @foreach($employees ?? [] as $employee)
                            <option value="{{ $employee->id }}" {{ old('assigned_to') == $employee->id ? 'selected' : '' }}>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Asset</button>
            </div>
        </form>
    </div>
</div>
@endsection
