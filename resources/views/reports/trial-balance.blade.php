@extends('layouts.app')
@section('title', 'Trial Balance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Trial Balance</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/trial-balance') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-control" value="{{ request('as_of_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header text-center">
        <h5 class="mb-0">Trial Balance as of {{ request('as_of_date', date('d M Y')) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
                @php $totalDebit = 0; $totalCredit = 0; @endphp
                @forelse($accounts ?? [] as $account)
                @php
                    $totalDebit += $account->debit_balance ?? 0;
                    $totalCredit += $account->credit_balance ?? 0;
                @endphp
                <tr>
                    <td><code>{{ $account->code }}</code></td>
                    <td>{{ $account->name }}</td>
                    <td class="text-end">{{ ($account->debit_balance ?? 0) > 0 ? number_format($account->debit_balance, 2) : '-' }}</td>
                    <td class="text-end">{{ ($account->credit_balance ?? 0) > 0 ? number_format($account->credit_balance, 2) : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="2" class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                </tr>
                @if(abs($totalDebit - $totalCredit) > 0.01)
                <tr class="table-danger">
                    <td colspan="2" class="fw-bold">Difference</td>
                    <td colspan="2" class="text-end fw-bold">{{ number_format(abs($totalDebit - $totalCredit), 2) }}</td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection
