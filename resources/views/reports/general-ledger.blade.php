@extends('layouts.app')
@section('title', 'General Ledger')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">General Ledger</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/general-ledger') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Account</label>
                <select name="account_id" class="form-select" required>
                    <option value="">Select Account</option>
                    @foreach($accounts ?? [] as $account)
                        <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

@if(isset($selectedAccount))
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ $selectedAccount->code }} - {{ $selectedAccount->name }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-info">
                    <td colspan="5" class="fw-bold">Opening Balance</td>
                    <td class="text-end fw-bold">{{ number_format($openingBalance ?? 0, 2) }}</td>
                </tr>
                @php $runningBalance = $openingBalance ?? 0; @endphp
                @forelse($transactions ?? [] as $txn)
                @php
                    $runningBalance += ($txn->debit ?? 0) - ($txn->credit ?? 0);
                @endphp
                <tr>
                    <td>{{ $txn->date?->format('d M Y') }}</td>
                    <td>{{ $txn->description ?? '-' }}</td>
                    <td>{{ $txn->reference ?? '-' }}</td>
                    <td class="text-end">{{ ($txn->debit ?? 0) > 0 ? number_format($txn->debit, 2) : '-' }}</td>
                    <td class="text-end">{{ ($txn->credit ?? 0) > 0 ? number_format($txn->credit, 2) : '-' }}</td>
                    <td class="text-end fw-bold">{{ number_format($runningBalance, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="3" class="fw-bold">Closing Balance</td>
                    <td class="text-end fw-bold">{{ number_format(collect($transactions ?? [])->sum('debit'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($transactions ?? [])->sum('credit'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($runningBalance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif
@endsection
