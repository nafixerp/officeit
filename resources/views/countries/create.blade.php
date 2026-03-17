@extends('layouts.app')

@section('title', 'Create Country')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Create Country</h1>
    <a href="{{ route('countries.index') }}" class="btn btn-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('countries.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="iso_code" class="form-label">ISO Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('iso_code') is-invalid @enderror" id="iso_code" name="iso_code" value="{{ old('iso_code') }}" maxlength="2" required>
                    @error('iso_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="iso3_code" class="form-label">ISO3 Code</label>
                    <input type="text" class="form-control @error('iso3_code') is-invalid @enderror" id="iso3_code" name="iso3_code" value="{{ old('iso3_code') }}" maxlength="3">
                    @error('iso3_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="phone_code" class="form-label">Phone Code</label>
                    <input type="text" class="form-control @error('phone_code') is-invalid @enderror" id="phone_code" name="phone_code" value="{{ old('phone_code') }}">
                    @error('phone_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="default_currency_id" class="form-label">Default Currency</label>
                    <select class="form-select @error('default_currency_id') is-invalid @enderror" id="default_currency_id" name="default_currency_id">
                        <option value="">Select Currency</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ old('default_currency_id') == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                        @endforeach
                    </select>
                    @error('default_currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tax_type" class="form-label">Tax Type</label>
                    <select class="form-select @error('tax_type') is-invalid @enderror" id="tax_type" name="tax_type">
                        <option value="">Select Tax Type</option>
                        @foreach(['VAT', 'GST', 'SALES_TAX', 'NONE'] as $type)
                            <option value="{{ $type }}" {{ old('tax_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('tax_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tax_percentage" class="form-label">Tax Percentage</label>
                    <input type="number" step="0.0001" class="form-control @error('tax_percentage') is-invalid @enderror" id="tax_percentage" name="tax_percentage" value="{{ old('tax_percentage') }}">
                    @error('tax_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tax_registration_format" class="form-label">Tax Registration Format</label>
                    <input type="text" class="form-control @error('tax_registration_format') is-invalid @enderror" id="tax_registration_format" name="tax_registration_format" value="{{ old('tax_registration_format') }}">
                    @error('tax_registration_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_number_format" class="form-label">Invoice Number Format</label>
                    <input type="text" class="form-control @error('invoice_number_format') is-invalid @enderror" id="invoice_number_format" name="invoice_number_format" value="{{ old('invoice_number_format') }}">
                    @error('invoice_number_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date_format" class="form-label">Date Format</label>
                    <input type="text" class="form-control @error('date_format') is-invalid @enderror" id="date_format" name="date_format" value="{{ old('date_format') }}">
                    @error('date_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="financial_year_format" class="form-label">Financial Year Format</label>
                    <input type="text" class="form-control @error('financial_year_format') is-invalid @enderror" id="financial_year_format" name="financial_year_format" value="{{ old('financial_year_format') }}">
                    @error('financial_year_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="address_style" class="form-label">Address Style</label>
                    <input type="text" class="form-control @error('address_style') is-invalid @enderror" id="address_style" name="address_style" value="{{ old('address_style') }}">
                    @error('address_style')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="accounting_rules_profile" class="form-label">Accounting Rules Profile</label>
                    <input type="text" class="form-control @error('accounting_rules_profile') is-invalid @enderror" id="accounting_rules_profile" name="accounting_rules_profile" value="{{ old('accounting_rules_profile') }}">
                    @error('accounting_rules_profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Country</button>
            <a href="{{ route('countries.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
