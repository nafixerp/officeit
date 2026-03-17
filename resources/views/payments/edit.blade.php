@extends('layouts.app')
@section('title', 'Edit Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Payment #{{ $payment->payment_number }}</h1>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('payments.update', $payment) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Payment Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $payment->date) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $payment->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" id="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required onchange="toggleBankAccount()">
                        <option value="">Select Mode</option>
                        @foreach(['Cash','Bank','Cheque','Transfer','Online'] as $mode)
                            <option value="{{ $mode }}" {{ old('payment_mode', $payment->payment_mode) == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                    @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror">
                        @foreach($currencies ?? ['USD','EUR','GBP','AED','SAR','INR'] as $cur)
                            <option value="{{ $cur }}" {{ old('currency', $payment->currency) == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                    @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label for="exchange_rate" class="form-label">Exchange Rate</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" class="form-control @error('exchange_rate') is-invalid @enderror" value="{{ old('exchange_rate', $payment->exchange_rate) }}" step="0.000001" min="0">
                    @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $payment->amount) }}" step="0.01" min="0" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="reference_number" class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $payment->reference_number) }}">
                    @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4" id="bankAccountField" style="{{ in_array(old('payment_mode', $payment->payment_mode), ['Cash','']) ? 'display:none;' : '' }}">
                    <label for="bank_account_id" class="form-label">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                        <option value="">Select Bank Account</option>
                        @foreach($bankAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('bank_account_id', $payment->bank_account_id) == $account->id ? 'selected' : '' }}>{{ $account->account_name }} - {{ $account->bank_name }}</option>
                        @endforeach
                    </select>
                    @error('bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control @error('narration') is-invalid @enderror" rows="3">{{ old('narration', $payment->narration) }}</textarea>
                    @error('narration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('payments.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="status" value="Draft" class="btn btn-outline-primary">Save as Draft</button>
        <button type="submit" name="status" value="Confirmed" class="btn btn-primary">Save &amp; Confirm</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleBankAccount() {
    var mode = document.getElementById('payment_mode').value;
    var bankField = document.getElementById('bankAccountField');
    if (mode === 'Cash' || mode === '') {
        bankField.style.display = 'none';
    } else {
        bankField.style.display = '';
    }
}
document.addEventListener('DOMContentLoaded', function() { toggleBankAccount(); });
</script>
@endsection
