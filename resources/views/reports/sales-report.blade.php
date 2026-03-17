@extends('layouts.app')
@section('title', 'Sales Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Sales Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/sales') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales ?? [] as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->date?->format('d M Y') }}</td>
                    <td>{{ $sale->customer->name ?? '-' }}</td>
                    <td class="text-end">{{ number_format($sale->subtotal ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->tax_amount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($sale->total ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No sales data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td colspan="3" class="fw-bold">Totals</td>
                    <td class="text-end fw-bold">{{ number_format(collect($sales ?? [])->sum('subtotal'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($sales ?? [])->sum('tax_amount'), 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format(collect($sales ?? [])->sum('total'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
