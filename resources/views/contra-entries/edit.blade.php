@extends('layouts.app')
@section('title', 'Edit Contra Entry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Contra Entry #{{ $contraEntry->entry_number }}</h1>
    <a href="{{ route('contra-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('contra-entries.update', $contraEntry) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Contra Entry Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $contraEntry->date) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="from_account_id" class="form-label">From Account (Bank/Cash) <span class="text-danger">*</span></label>
                    <select name="from_account_id" id="from_account_id" class="form-select" required>
                        <option value="">Select Account</option>
                        @foreach($accounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('from_account_id', $contraEntry->from_account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="to_account_id" class="form-label">To Account <span class="text-danger">*</span></label>
                    <select name="to_account_id" id="to_account_id" class="form-select" required>
                        <option value="">Select Account</option>
                        @foreach($accounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('to_account_id', $contraEntry->to_account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', $contraEntry->amount) }}" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-12">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control" rows="2">{{ old('narration', $contraEntry->narration) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('contra-entries.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Contra Entry</button>
    </div>
</form>
@endsection
