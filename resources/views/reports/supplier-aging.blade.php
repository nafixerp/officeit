@extends('layouts.app')
@section('title', 'Supplier Aging Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Supplier Aging Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/supplier-aging') }}" class="row g-3">
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
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Supplier</th>
                    <th class="text-end">0-30 Days</th>
                    <th class="text-end">31-60 Days</th>
                    <th class="text-end">61-90 Days</th>
                    <th class="text-end">90+ Days</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agingData ?? [] as $data)
                <tr>
                    <td>{{ $data['supplier_name'] ?? $data['name'] ?? '-' }}</td>
                    <td class="text-end">{{ number_format($data['days_0_30'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($data['days_31_60'] ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($data['days_61_90'] ?? 0, 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($data['days_90_plus'] ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(($data['days_0_30'] ?? 0) + ($data['days_31_60'] ?? 0) + ($data['days_61_90'] ?? 0) + ($data['days_90_plus'] ?? 0), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No aging data available.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($agingData ?? [])->sum('days_0_30'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($agingData ?? [])->sum('days_31_60'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($agingData ?? [])->sum('days_61_90'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($agingData ?? [])->sum('days_90_plus'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($agingData ?? [])->sum(fn($d) => ($d['days_0_30'] ?? 0) + ($d['days_31_60'] ?? 0) + ($d['days_61_90'] ?? 0) + ($d['days_90_plus'] ?? 0)), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
