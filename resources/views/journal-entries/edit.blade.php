@extends('layouts.app')
@section('title', 'Edit Journal Entry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Journal Entry #{{ $journalEntry->entry_number }}</h1>
    <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('journal-entries.update', $journalEntry) }}" method="POST" id="journalForm">
    @csrf
    @method('PUT')

    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Entry Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $journalEntry->date) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        @foreach(['General','Adjusting','Closing','Reversing','Opening'] as $type)
                            <option value="{{ $type }}" {{ old('type', $journalEntry->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select @error('currency') is-invalid @enderror">
                        @foreach($currencies ?? ['USD','EUR','GBP','AED','SAR','INR'] as $cur)
                            <option value="{{ $cur }}" {{ old('currency', $journalEntry->currency) == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                    @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="reference_number" class="form-label">Reference</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number', $journalEntry->reference_number) }}">
                    @error('reference_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control @error('narration') is-invalid @enderror" rows="2">{{ old('narration', $journalEntry->narration) }}</textarea>
                    @error('narration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Lines --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Journal Lines</h5>
            <button type="button" class="btn btn-sm btn-success" onclick="addLine()"><i class="fas fa-plus"></i> Add Line</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:30%">Account <span class="text-danger">*</span></th>
                        <th style="width:15%">Debit</th>
                        <th style="width:15%">Credit</th>
                        <th style="width:30%">Narration</th>
                        <th style="width:10%"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    @foreach($journalEntry->lines as $idx => $line)
                    <tr class="line-row" data-index="{{ $idx }}">
                        <td>
                            <select name="lines[{{ $idx }}][account_id]" class="form-select form-select-sm" required>
                                <option value="">Select Account</option>
                                @foreach($accounts ?? [] as $account)
                                    <option value="{{ $account->id }}" {{ old("lines.$idx.account_id", $line->account_id) == $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="lines[{{ $idx }}][debit]" class="form-control form-control-sm debit" value="{{ old("lines.$idx.debit", $line->debit) }}" min="0" step="0.01" onchange="calculateTotals()"></td>
                        <td><input type="number" name="lines[{{ $idx }}][credit]" class="form-control form-control-sm credit" value="{{ old("lines.$idx.credit", $line->credit) }}" min="0" step="0.01" onchange="calculateTotals()"></td>
                        <td><input type="text" name="lines[{{ $idx }}][narration]" class="form-control form-control-sm" value="{{ old("lines.$idx.narration", $line->narration) }}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-warning">
                    <tr>
                        <td class="fw-bold text-end">Totals:</td>
                        <td class="fw-bold text-end" id="totalDebit">0.00</td>
                        <td class="fw-bold text-end" id="totalCredit">0.00</td>
                        <td colspan="2">
                            <span id="balanceMessage" class="fw-bold"></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <input type="hidden" name="total_amount" id="totalAmountInput">

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="status" value="Draft" class="btn btn-outline-primary">Save as Draft</button>
        <button type="submit" name="status" value="Posted" class="btn btn-primary">Post Entry</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
let lineIndex = {{ count($journalEntry->lines) }};

function addLine() {
    var tbody = document.getElementById('linesBody');
    var firstRow = tbody.querySelector('.line-row');
    var newRow = firstRow.cloneNode(true);
    newRow.setAttribute('data-index', lineIndex);
    newRow.querySelectorAll('select, input').forEach(function(el) {
        var name = el.getAttribute('name');
        if (name) el.setAttribute('name', name.replace(/\[\d+\]/, '[' + lineIndex + ']'));
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else if (el.type !== 'button') el.value = el.classList.contains('debit') || el.classList.contains('credit') ? '0' : '';
    });
    tbody.appendChild(newRow);
    lineIndex++;
}

function removeLine(btn) {
    var tbody = document.getElementById('linesBody');
    if (tbody.querySelectorAll('.line-row').length > 2) {
        btn.closest('tr').remove();
        calculateTotals();
    }
}

function calculateTotals() {
    var totalDebit = 0, totalCredit = 0;
    document.querySelectorAll('#linesBody .line-row').forEach(function(row) {
        totalDebit += parseFloat(row.querySelector('.debit').value) || 0;
        totalCredit += parseFloat(row.querySelector('.credit').value) || 0;
    });
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
    document.getElementById('totalAmountInput').value = totalDebit.toFixed(2);

    var msg = document.getElementById('balanceMessage');
    var diff = Math.abs(totalDebit - totalCredit);
    if (diff < 0.01) {
        msg.textContent = 'Balanced';
        msg.className = 'fw-bold text-success';
    } else {
        msg.textContent = 'Difference: ' + diff.toFixed(2);
        msg.className = 'fw-bold text-danger';
    }
}

document.addEventListener('DOMContentLoaded', function() { calculateTotals(); });
</script>
@endsection
