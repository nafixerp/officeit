@extends('layouts.app')
@section('title', 'Day Book')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Day Book</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/day-book') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> View</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Transactions for {{ request('date', date('d M Y')) }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $txn)
                <tr>
                    <td>{{ $txn->created_at?->format('H:i') }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($txn->type ?? '-') }}</span></td>
                    <td>{{ $txn->reference ?? '-' }}</td>
                    <td>{{ $txn->account->name ?? '-' }}</td>
                    <td>{{ $txn->description ?? '-' }}</td>
                    <td class="text-end">{{ ($txn->debit ?? 0) > 0 ? number_format($txn->debit, 2) : '-' }}</td>
                    <td class="text-end">{{ ($txn->credit ?? 0) > 0 ? number_format($txn->credit, 2) : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No transactions for this date.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="5" class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($transactions ?? [])->sum('debit'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($transactions ?? [])->sum('credit'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
