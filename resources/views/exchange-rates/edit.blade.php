@extends('layouts.app')

@section('title', 'Edit Exchange Rate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Edit Exchange Rate</h1>
    <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('exchange-rates.update', $exchangeRate) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="from_currency_id" class="form-label">From Currency <span class="text-danger">*</span></label>
                    <select class="form-select @error('from_currency_id') is-invalid @enderror" id="from_currency_id" name="from_currency_id" required>
                        <option value="">Select Currency</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ old('from_currency_id', $exchangeRate->from_currency_id) == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                        @endforeach
                    </select>
                    @error('from_currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="to_currency_id" class="form-label">To Currency <span class="text-danger">*</span></label>
                    <select class="form-select @error('to_currency_id') is-invalid @enderror" id="to_currency_id" name="to_currency_id" required>
                        <option value="">Select Currency</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ old('to_currency_id', $exchangeRate->to_currency_id) == $currency->id ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                        @endforeach
                    </select>
                    @error('to_currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="rate" class="form-label">Rate <span class="text-danger">*</span></label>
                    <input type="number" step="0.000001" class="form-control @error('rate') is-invalid @enderror" id="rate" name="rate" value="{{ old('rate', $exchangeRate->rate) }}" required>
                    @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('effective_date') is-invalid @enderror" id="effective_date" name="effective_date" value="{{ old('effective_date', $exchangeRate->effective_date->format('Y-m-d')) }}" required>
                    @error('effective_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Exchange Rate</button>
            <a href="{{ route('exchange-rates.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
