@extends('layouts.app')
@section('title', 'Cash Flow Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Cash Flow Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/cash-flow') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Month</th>
                    <th class="text-end">Receipts</th>
                    <th class="text-end">Payments</th>
                    <th class="text-end">Net Cash Flow</th>
                    <th class="text-end">Cumulative</th>
                </tr>
            </thead>
            <tbody>
                @php $cumulative = 0; @endphp
                @forelse($cashFlowData ?? [] as $data)
                @php
                    $net = ($data['receipts'] ?? 0) - ($data['payments'] ?? 0);
                    $cumulative += $net;
                @endphp
                <tr>
                    <td>{{ $data['month'] ?? '-' }}</td>
                    <td class="text-end text-success">{{ number_format($data['receipts'] ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($data['payments'] ?? 0, 2) }}</td>
                    <td class="text-end fw-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($net, 2) }}</td>
                    <td class="text-end {{ $cumulative >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($cumulative, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($cashFlowData ?? [])->sum('receipts'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($cashFlowData ?? [])->sum('payments'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($cashFlowData ?? [])->sum('receipts') - collect($cashFlowData ?? [])->sum('payments'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
