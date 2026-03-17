@extends('layouts.app')
@section('title', 'Create Journal Entry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Create Journal Entry</h1>
    <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('journal-entries.store') }}" method="POST" id="journalForm">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Entry Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="General" {{ old('type') == 'General' ? 'selected' : '' }}>General</option>
                        <option value="Adjustment" {{ old('type') == 'Adjustment' ? 'selected' : '' }}>Adjustment</option>
                        <option value="Opening" {{ old('type') == 'Opening' ? 'selected' : '' }}>Opening</option>
                        <option value="Closing" {{ old('type') == 'Closing' ? 'selected' : '' }}>Closing</option>
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
                    <label for="project_id" class="form-label">Project</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="">Select Project</option>
                        @foreach($projects ?? [] as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="cost_center_id" class="form-label">Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" class="form-select">
                        <option value="">Select</option>
                        @foreach($costCenters ?? [] as $cc)
                            <option value="{{ $cc->id }}" {{ old('cost_center_id') == $cc->id ? 'selected' : '' }}>{{ $cc->name }}</option>
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

    {{-- Journal Lines --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Journal Lines</h5>
            <button type="button" class="btn btn-sm btn-success" onclick="addLine()"><i class="bi bi-plus-lg"></i> Add Line</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="linesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:25%">Account</th>
                        <th style="width:15%">Debit Amount</th>
                        <th style="width:15%">Credit Amount</th>
                        <th style="width:20%">Narration</th>
                        <th style="width:10%">Cost Center</th>
                        <th style="width:10%">Project</th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    <tr class="line-row" data-index="0">
                        <td>
                            <select name="lines[0][account_id]" class="form-select form-select-sm" required>
                                <option value="">Select Account</option>
                                @foreach($accounts ?? [] as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="lines[0][debit]" class="form-control form-control-sm debit" value="0" min="0" step="0.01" onchange="calculateJournalTotals()"></td>
                        <td><input type="number" name="lines[0][credit]" class="form-control form-control-sm credit" value="0" min="0" step="0.01" onchange="calculateJournalTotals()"></td>
                        <td><input type="text" name="lines[0][narration]" class="form-control form-control-sm"></td>
                        <td>
                            <select name="lines[0][cost_center_id]" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($costCenters ?? [] as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[0][project_id]" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                    <tr class="line-row" data-index="1">
                        <td>
                            <select name="lines[1][account_id]" class="form-select form-select-sm" required>
                                <option value="">Select Account</option>
                                @foreach($accounts ?? [] as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="lines[1][debit]" class="form-control form-control-sm debit" value="0" min="0" step="0.01" onchange="calculateJournalTotals()"></td>
                        <td><input type="number" name="lines[1][credit]" class="form-control form-control-sm credit" value="0" min="0" step="0.01" onchange="calculateJournalTotals()"></td>
                        <td><input type="text" name="lines[1][narration]" class="form-control form-control-sm"></td>
                        <td>
                            <select name="lines[1][cost_center_id]" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($costCenters ?? [] as $cc)
                                    <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[1][project_id]" class="form-select form-select-sm">
                                <option value="">-</option>
                                @foreach($projects ?? [] as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="bi bi-x"></i></button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td class="text-end">Totals:</td>
                        <td id="totalDebit" class="text-end">0.00</td>
                        <td id="totalCredit" class="text-end">0.00</td>
                        <td colspan="4">
                            <span id="differenceLabel" class="text-success">Balanced</span>
                            <span id="differenceAmount" class="d-none text-danger fw-bold"></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" name="status" value="Draft" class="btn btn-outline-primary">Save as Draft</button>
        <button type="submit" name="status" value="Posted" class="btn btn-primary">Post Entry</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let lineIndex = 2;

function addLine() {
    const tbody = document.getElementById('linesBody');
    const firstRow = tbody.querySelector('.line-row');
    const newRow = firstRow.cloneNode(true);
    newRow.setAttribute('data-index', lineIndex);
    newRow.querySelectorAll('select, input').forEach(el => {
        const name = el.getAttribute('name');
        if (name) el.setAttribute('name', name.replace(/\[\d+\]/, '[' + lineIndex + ']'));
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else if (el.type !== 'button') el.value = el.classList.contains('debit') || el.classList.contains('credit') ? '0' : '';
    });
    tbody.appendChild(newRow);
    lineIndex++;
}

function removeLine(btn) {
    const tbody = document.getElementById('linesBody');
    if (tbody.querySelectorAll('.line-row').length > 2) {
        btn.closest('tr').remove();
        calculateJournalTotals();
    }
}

function calculateJournalTotals() {
    let totalDebit = 0, totalCredit = 0;
    document.querySelectorAll('#linesBody .line-row').forEach(row => {
        totalDebit += parseFloat(row.querySelector('.debit').value) || 0;
        totalCredit += parseFloat(row.querySelector('.credit').value) || 0;
    });

    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

    const diff = Math.abs(totalDebit - totalCredit);
    const label = document.getElementById('differenceLabel');
    const amount = document.getElementById('differenceAmount');

    if (diff < 0.01) {
        label.textContent = 'Balanced';
        label.className = 'text-success';
        amount.classList.add('d-none');
    } else {
        label.textContent = 'Difference: ';
        label.className = 'text-danger';
        amount.textContent = diff.toFixed(2);
        amount.classList.remove('d-none');
    }
}
</script>
@endpush
