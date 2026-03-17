@extends('layouts.app')
@section('title', 'Create Quotation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Create Quotation</h1>
    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('quotations.store') }}" method="POST" id="quotationForm">
    @csrf

    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Quotation Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">Select Customer</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                    <select name="currency" id="currency" class="form-select" required>
                        @foreach($currencies ?? ['USD','EUR','GBP','AED','SAR','INR'] as $cur)
                            <option value="{{ $cur }}" {{ old('currency', 'USD') == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="exchange_rate" class="form-label">Exchange Rate</label>
                    <input type="number" name="exchange_rate" id="exchange_rate" class="form-control" value="{{ old('exchange_rate', 1) }}" step="0.000001" min="0">
                </div>
                <div class="col-md-2">
                    <label for="branch_id" class="form-label">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-select">
                        <option value="">Select Branch</option>
                        @foreach($branches ?? [] as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="validity_date" class="form-label">Valid Until</label>
                    <input type="date" name="validity_date" id="validity_date" class="form-control" value="{{ old('validity_date') }}">
                </div>
                <div class="col-md-10">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Line Items</h5>
            <button type="button" class="btn btn-sm btn-success" onclick="addRow()"><i class="bi bi-plus-lg"></i> Add Row</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:20%">Item / Service</th>
                        <th style="width:15%">Description</th>
                        <th style="width:8%">Qty</th>
                        <th style="width:10%">Rate</th>
                        <th style="width:8%">Discount</th>
                        <th style="width:12%">Tax Rate</th>
                        <th style="width:10%">Tax Amt</th>
                        <th style="width:12%">Net Amount</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr class="item-row" data-index="0">
                        <td>
                            <select name="items[0][item_id]" class="form-select form-select-sm">
                                <option value="">Select Item</option>
                                @foreach($items ?? [] as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="items[0][description]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm qty" value="1" min="0" step="any" onchange="calculateRow(this)"></td>
                        <td><input type="number" name="items[0][rate]" class="form-control form-control-sm rate" value="0" min="0" step="any" onchange="calculateRow(this)"></td>
                        <td><input type="number" name="items[0][discount]" class="form-control form-control-sm discount" value="0" min="0" step="any" onchange="calculateRow(this)"></td>
                        <td>
                            <select name="items[0][tax_rate_id]" class="form-select form-select-sm tax-rate" onchange="calculateRow(this)">
                                <option value="" data-rate="0">No Tax</option>
                                @foreach($taxRates ?? [] as $tax)
                                    <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm tax-amount" readonly value="0.00"></td>
                        <td><input type="text" class="form-control form-control-sm net-amount" readonly value="0.00"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Totals --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <table class="table table-sm">
                        <tr>
                            <td class="fw-bold">Subtotal:</td>
                            <td class="text-end" id="subtotal">0.00</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Tax:</td>
                            <td class="text-end" id="totalTax">0.00</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Discount:</td>
                            <td class="text-end" id="totalDiscount">0.00</td>
                        </tr>
                        <tr class="table-primary">
                            <td class="fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold" id="grandTotal">0.00</td>
                        </tr>
                    </table>
                    <input type="hidden" name="subtotal" id="subtotalInput">
                    <input type="hidden" name="total_tax" id="totalTaxInput">
                    <input type="hidden" name="total_discount" id="totalDiscountInput">
                    <input type="hidden" name="total_amount" id="grandTotalInput">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('quotations.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="status" value="Draft" class="btn btn-outline-primary">Save as Draft</button>
        <button type="submit" name="status" value="Sent" class="btn btn-primary">Save &amp; Send</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let rowIndex = 1;

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const firstRow = tbody.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);
    newRow.setAttribute('data-index', rowIndex);

    newRow.querySelectorAll('select, input').forEach(el => {
        const name = el.getAttribute('name');
        if (name) {
            el.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
        }
        if (el.tagName === 'SELECT') {
            el.selectedIndex = 0;
        } else if (el.type !== 'button') {
            el.value = el.classList.contains('qty') ? '1' : (el.readOnly ? '0.00' : '0');
        }
    });

    tbody.appendChild(newRow);
    rowIndex++;
}

function removeRow(btn) {
    const tbody = document.getElementById('itemsBody');
    if (tbody.querySelectorAll('.item-row').length > 1) {
        btn.closest('tr').remove();
        calculateTotals();
    }
}

function calculateRow(el) {
    const row = el.closest('tr');
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const rate = parseFloat(row.querySelector('.rate').value) || 0;
    const discount = parseFloat(row.querySelector('.discount').value) || 0;
    const taxSelect = row.querySelector('.tax-rate');
    const taxPercent = parseFloat(taxSelect.options[taxSelect.selectedIndex].getAttribute('data-rate')) || 0;

    const lineTotal = (qty * rate) - discount;
    const taxAmount = lineTotal * (taxPercent / 100);
    const netAmount = lineTotal + taxAmount;

    row.querySelector('.tax-amount').value = taxAmount.toFixed(2);
    row.querySelector('.net-amount').value = netAmount.toFixed(2);

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0, totalTax = 0, totalDiscount = 0;

    document.querySelectorAll('#itemsBody .item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const discount = parseFloat(row.querySelector('.discount').value) || 0;
        const taxAmt = parseFloat(row.querySelector('.tax-amount').value) || 0;

        subtotal += qty * rate;
        totalDiscount += discount;
        totalTax += taxAmt;
    });

    const grandTotal = subtotal - totalDiscount + totalTax;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('totalTax').textContent = totalTax.toFixed(2);
    document.getElementById('totalDiscount').textContent = totalDiscount.toFixed(2);
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);

    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('totalTaxInput').value = totalTax.toFixed(2);
    document.getElementById('totalDiscountInput').value = totalDiscount.toFixed(2);
    document.getElementById('grandTotalInput').value = grandTotal.toFixed(2);
}
</script>
@endpush
