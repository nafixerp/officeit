@extends('layouts.app')

@section('title', 'Edit Currency')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Edit Currency: {{ $currency->code }}</h1>
    <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('currencies.update', $currency) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $currency->code) }}" maxlength="3" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $currency->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="symbol" class="form-label">Symbol</label>
                    <input type="text" class="form-control @error('symbol') is-invalid @enderror" id="symbol" name="symbol" value="{{ old('symbol', $currency->symbol) }}">
                    @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="decimal_places" class="form-label">Decimal Places</label>
                    <input type="number" class="form-control @error('decimal_places') is-invalid @enderror" id="decimal_places" name="decimal_places" value="{{ old('decimal_places', $currency->decimal_places) }}" min="0" max="6">
                    @error('decimal_places')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_base_currency" name="is_base_currency" value="1" {{ old('is_base_currency', $currency->is_base_currency) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_base_currency">Base Currency</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Currency</button>
            <a href="{{ route('currencies.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
