@extends('layouts.app')
@section('title', 'Create Receipt')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Create Receipt</h1>
    <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('receipts.store') }}" method="POST" id="receiptForm">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Receipt Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="customer_id" class="form-label">Received From (Customer) <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customer_id" class="form-select" required onchange="loadUnpaidInvoices()">
                        <option value="">Select Customer</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="receipt_mode" class="form-label">Receipt Mode <span class="text-danger">*</span></label>
                    <select name="receipt_mode" id="receipt_mode" class="form-select" required onchange="toggleAccountFields()">
                        <option value="Cash" {{ old('receipt_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="Bank" {{ old('receipt_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                        <option value="Cheque" {{ old('receipt_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="Transfer" {{ old('receipt_mode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="Online" {{ old('receipt_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select">
                        @foreach($currencies ?? ['USD','EUR','GBP','AED','SAR','INR'] as $cur)
                            <option value="{{ $cur }}" {{ old('currency', 'USD') == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label for="exchange_rate" class="form-label">Ex. Rate</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" class="form-control" value="{{ old('exchange_rate', 1) }}" step="0.000001" min="0">
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" step="0.01" min="0" required>
                </div>
                <div class="col-md-3">
                    <label for="reference_number" class="form-label">Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control" value="{{ old('reference_number') }}">
                </div>
                <div class="col-md-3" id="bankAccountField">
                    <label for="bank_account_id" class="form-label">Bank Account</label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select">
                        <option value="">Select Bank Account</option>
                        @foreach($bankAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" id="cashAccountField" style="display:none;">
                    <label for="cash_account_id" class="form-label">Cash Account</label>
                    <select name="cash_account_id" id="cash_account_id" class="form-select">
                        <option value="">Select Cash Account</option>
                        @foreach($cashAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('cash_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control" rows="2">{{ old('narration') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice Allocation --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Invoice Allocation</h5></div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="allocationTable">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th><th>Date</th><th class="text-end">Total</th>
                        <th class="text-end">Paid</th><th class="text-end">Balance</th>
                        <th style="width:15%">Allocate Amount</th>
                    </tr>
                </thead>
                <tbody id="allocationBody">
                    @forelse($unpaidInvoices ?? [] as $inv)
                    <tr>
                        <td>{{ $inv->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($inv->date)->format('d M Y') }}</td>
                        <td class="text-end">{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($inv->total_amount - $inv->paid_amount, 2) }}</td>
                        <td>
                            <input type="hidden" name="allocations[{{ $inv->id }}][invoice_id]" value="{{ $inv->id }}">
                            <input type="number" name="allocations[{{ $inv->id }}][amount]" class="form-control form-control-sm allocation-amount" value="0" min="0" max="{{ $inv->total_amount - $inv->paid_amount }}" step="0.01" onchange="updateAllocationTotal()">
                        </td>
                    </tr>
                    @empty
                    <tr id="noInvoicesRow"><td colspan="6" class="text-center text-muted">Select a customer to load unpaid invoices.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-info">
                        <td colspan="5" class="text-end fw-bold">Total Allocated:</td>
                        <td class="fw-bold" id="totalAllocated">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('receipts.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="status" value="Draft" class="btn btn-outline-primary">Save as Draft</button>
        <button type="submit" name="status" value="Confirmed" class="btn btn-primary">Confirm Receipt</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleAccountFields() {
    const mode = document.getElementById('receipt_mode').value;
    const bankField = document.getElementById('bankAccountField');
    const cashField = document.getElementById('cashAccountField');

    if (mode === 'Cash') {
        bankField.style.display = 'none';
        cashField.style.display = 'block';
    } else {
        bankField.style.display = 'block';
        cashField.style.display = 'none';
    }
}

function loadUnpaidInvoices() {
    const customerId = document.getElementById('customer_id').value;
    if (!customerId) return;

    fetch(`/api/customers/${customerId}/unpaid-invoices`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('allocationBody');
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No unpaid invoices for this customer.</td></tr>';
                return;
            }
            data.forEach(inv => {
                const balance = (inv.total_amount - inv.paid_amount).toFixed(2);
                tbody.innerHTML += `<tr>
                    <td>${inv.invoice_number}</td>
                    <td>${inv.date}</td>
                    <td class="text-end">${parseFloat(inv.total_amount).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(inv.paid_amount).toFixed(2)}</td>
                    <td class="text-end">${balance}</td>
                    <td>
                        <input type="hidden" name="allocations[${inv.id}][invoice_id]" value="${inv.id}">
                        <input type="number" name="allocations[${inv.id}][amount]" class="form-control form-control-sm allocation-amount" value="0" min="0" max="${balance}" step="0.01" onchange="updateAllocationTotal()">
                    </td>
                </tr>`;
            });
        })
        .catch(() => {});
}

function updateAllocationTotal() {
    let total = 0;
    document.querySelectorAll('.allocation-amount').forEach(el => {
        total += parseFloat(el.value) || 0;
    });
    document.getElementById('totalAllocated').textContent = total.toFixed(2);
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    toggleAccountFields();
    updateAllocationTotal();
});
</script>
@endpush
