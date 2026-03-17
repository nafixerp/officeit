@extends('layouts.app')
@section('title', 'Edit Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Expense</h1>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Expense Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $expense->date) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="">Select Category</option>
                        @foreach($categories ?? ['Office Supplies','Travel','Utilities','Rent','Maintenance','Marketing','Insurance','Professional Fees','Miscellaneous'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $expense->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" id="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $expense->description) }}" required>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" required>
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="tax_rate_id" class="form-label">Tax Rate</label>
                    <select name="tax_rate_id" id="tax_rate_id" class="form-select @error('tax_rate_id') is-invalid @enderror">
                        <option value="">No Tax</option>
                        @foreach($taxRates ?? [] as $tax)
                            <option value="{{ $tax->id }}" {{ old('tax_rate_id', $expense->tax_rate_id) == $tax->id ? 'selected' : '' }}>{{ $tax->name }} ({{ $tax->rate }}%)</option>
                        @endforeach
                    </select>
                    @error('tax_rate_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" id="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required onchange="toggleAccountField()">
                        <option value="">Select Mode</option>
                        @foreach(['Cash','Bank','Cheque','Transfer','Online'] as $mode)
                            <option value="{{ $mode }}" {{ old('payment_mode', $expense->payment_mode) == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                    @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3" id="bankAccountField" style="{{ in_array(old('payment_mode', $expense->payment_mode), ['Cash','']) ? 'display:none;' : '' }}">
                    <label for="bank_account_id" class="form-label">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                        <option value="">Select Bank Account</option>
                        @foreach($bankAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('bank_account_id', $expense->bank_account_id) == $account->id ? 'selected' : '' }}>{{ $account->account_name }} - {{ $account->bank_name }}</option>
                        @endforeach
                    </select>
                    @error('bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="branch_id" class="form-label">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                        <option value="">Select Branch</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="cost_center_id" class="form-label">Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" class="form-select @error('cost_center_id') is-invalid @enderror">
                        <option value="">Select Cost Center</option>
                        @foreach($costCenters ?? [] as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id', $expense->cost_center_id) == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                        @endforeach
                    </select>
                    @error('cost_center_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="project_id" class="form-label">Project</label>
                    <select name="project_id" id="project_id" class="form-select @error('project_id') is-invalid @enderror">
                        <option value="">Select Project</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="attachment" class="form-label">Attachment</label>
                    <input type="file" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror">
                    @if($expense->attachment)
                        <small class="text-muted">Current: <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank">View File</a></small>
                    @endif
                    @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_recurring" id="is_recurring" class="form-check-input" value="1" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}>
                        <label for="is_recurring" class="form-check-label">Is Recurring</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Expense</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleAccountField() {
    var mode = document.getElementById('payment_mode').value;
    var bankField = document.getElementById('bankAccountField');
    if (mode === 'Cash' || mode === '') {
        bankField.style.display = 'none';
    } else {
        bankField.style.display = '';
    }
}
document.addEventListener('DOMContentLoaded', function() { toggleAccountField(); });
</script>
@endsection
