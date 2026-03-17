@extends('layouts.app')
@section('title', 'VAT Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">VAT Report</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/vat') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Output Tax (Sales)</h5></div>
            <div class="card-body text-center">
                <h2>{{ number_format($outputTax ?? 0, 2) }}</h2>
                <p class="text-muted mb-0">Tax collected on sales</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white"><h5 class="mb-0">Input Tax (Purchases)</h5></div>
            <div class="card-body text-center">
                <h2>{{ number_format($inputTax ?? 0, 2) }}</h2>
                <p class="text-muted mb-0">Tax paid on purchases</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white"><h5 class="mb-0">Net Tax Payable</h5></div>
            <div class="card-body text-center">
                @php $netTax = ($outputTax ?? 0) - ($inputTax ?? 0); @endphp
                <h2 class="{{ $netTax >= 0 ? 'text-danger' : 'text-success' }}">{{ number_format(abs($netTax), 2) }}</h2>
                <p class="text-muted mb-0">{{ $netTax >= 0 ? 'Amount Payable' : 'Amount Refundable' }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Detail Tables -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Output Tax Details (Sales)</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Invoice #</th><th>Date</th><th>Customer</th><th class="text-end">Tax Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($outputTaxDetails ?? [] as $detail)
                        <tr>
                            <td>{{ $detail->invoice_number }}</td>
                            <td>{{ $detail->date?->format('d M Y') }}</td>
                            <td>{{ $detail->customer->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($detail->tax_amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Input Tax Details (Purchases)</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Invoice #</th><th>Date</th><th>Supplier</th><th class="text-end">Tax Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse($inputTaxDetails ?? [] as $detail)
                        <tr>
                            <td>{{ $detail->invoice_number }}</td>
                            <td>{{ $detail->date?->format('d M Y') }}</td>
                            <td>{{ $detail->supplier->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($detail->tax_amount ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
