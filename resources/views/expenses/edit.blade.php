@extends('layouts.app')
@section('title', 'Edit Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Expense</h1>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" id="expenseForm">
    @csrf
    @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Expense Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $expense->date) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" id="description" class="form-control" value="{{ old('description', $expense->description) }}" required>
                </div>
                <div class="col-md-2">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" required onchange="calcTotal()">
                </div>
                <div class="col-md-2">
                    <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" id="tax_rate" class="form-control" value="{{ old('tax_rate', $expense->tax_rate) }}" step="0.01" min="0" onchange="calcTotal()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tax Amount</label>
                    <input type="text" id="taxAmountDisplay" class="form-control" readonly value="{{ number_format($expense->tax_amount, 2) }}">
                    <input type="hidden" name="tax_amount" id="tax_amount">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" id="totalDisplay" class="form-control fw-bold" readonly value="{{ number_format($expense->total_amount, 2) }}">
                    <input type="hidden" name="total_amount" id="total_amount">
                </div>
                <div class="col-md-2">
                    <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select name="payment_mode" id="payment_mode" class="form-select" required onchange="toggleExpenseAccount()">
                        @foreach(['Cash','Bank','Cheque','Transfer'] as $mode)
                            <option value="{{ $mode }}" {{ old('payment_mode', $expense->payment_mode) == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" id="bankAccField">
                    <label for="bank_account_id" class="form-label">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($bankAccounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('bank_account_id', $expense->bank_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" id="cashAccField" style="display:none;">
                    <label for="cash_account_id" class="form-label">Cash Account</label>
                    <select name="cash_account_id" id="cash_account_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($cashAccounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ old('cash_account_id', $expense->cash_account_id) == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="branch_id" class="form-label">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-select">
                        <option value="">Select Branch</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $expense->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cost_center_id" class="form-label">Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($costCenters ?? [] as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id', $expense->cost_center_id) == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="project_id" class="form-label">Project</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $expense->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="attachment" class="form-label">Attachment</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                    @if($expense->attachment)
                        <small class="text-muted">Current: {{ basename($expense->attachment) }}</small>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="is_recurring" id="is_recurring" class="form-check-input" value="1" {{ old('is_recurring', $expense->is_recurring) ? 'checked' : '' }}>
                        <label for="is_recurring" class="form-check-label">Recurring Expense</label>
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

@push('scripts')
<script>
function calcTotal() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const taxAmount = amount * (taxRate / 100);
    const total = amount + taxAmount;
    document.getElementById('taxAmountDisplay').value = taxAmount.toFixed(2);
    document.getElementById('tax_amount').value = taxAmount.toFixed(2);
    document.getElementById('totalDisplay').value = total.toFixed(2);
    document.getElementById('total_amount').value = total.toFixed(2);
}
function toggleExpenseAccount() {
    const mode = document.getElementById('payment_mode').value;
    document.getElementById('bankAccField').style.display = mode === 'Cash' ? 'none' : 'block';
    document.getElementById('cashAccField').style.display = mode === 'Cash' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', function() { toggleExpenseAccount(); calcTotal(); });
</script>
@endpush
