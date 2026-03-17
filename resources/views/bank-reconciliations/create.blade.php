@extends('layouts.app')
@section('title', 'New Bank Reconciliation')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Bank Reconciliation</h1>
    <a href="{{ route('bank-reconciliations.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('bank-reconciliations.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                    <select name="bank_account_id" id="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror" required>
                        <option value="">Select Bank Account</option>
                        @foreach($bankAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}
                                data-balance="{{ $account->current_balance ?? 0 }}">
                                {{ $account->name ?? $account->account_name }} ({{ $account->account_number }})
                            </option>
                        @endforeach
                    </select>
                    @error('bank_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Reconciliation Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="statement_balance" class="form-label">Statement Balance <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="statement_balance" id="statement_balance" class="form-control @error('statement_balance') is-invalid @enderror" value="{{ old('statement_balance') }}" required>
                    @error('statement_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Balance Comparison -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Book Balance</h6>
                            <h3 id="bookBalance">0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Statement Balance</h6>
                            <h3 id="stmtBalance">0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card" id="differenceCard">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Difference</h6>
                            <h3 id="difference">0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Reconciliation</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bankSelect = document.getElementById('bank_account_id');
    const stmtInput = document.getElementById('statement_balance');
    const bookEl = document.getElementById('bookBalance');
    const stmtEl = document.getElementById('stmtBalance');
    const diffEl = document.getElementById('difference');
    const diffCard = document.getElementById('differenceCard');

    function update() {
        const option = bankSelect.options[bankSelect.selectedIndex];
        const bookBal = parseFloat(option?.dataset?.balance || 0);
        const stmtBal = parseFloat(stmtInput.value || 0);
        const diff = stmtBal - bookBal;

        bookEl.textContent = bookBal.toFixed(2);
        stmtEl.textContent = stmtBal.toFixed(2);
        diffEl.textContent = diff.toFixed(2);
        diffCard.className = diff === 0 ? 'card bg-success bg-opacity-25' : 'card bg-danger bg-opacity-25';
    }

    bankSelect.addEventListener('change', update);
    stmtInput.addEventListener('input', update);
});
</script>
@endpush
