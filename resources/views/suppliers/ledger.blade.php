@extends('layouts.app')

@section('title', 'Supplier Ledger')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Supplier Ledger: {{ $supplier->name }}</h1>
    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">Back to Supplier</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('suppliers.ledger', $supplier) }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('suppliers.ledger', $supplier) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Voucher Type</th>
                        <th>Voucher #</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries ?? [] as $entry)
                        <tr>
                            <td>{{ $entry->date->format('Y-m-d') }}</td>
                            <td>{{ $entry->voucher_type }}</td>
                            <td>{{ $entry->voucher_number }}</td>
                            <td class="text-end">{{ number_format($entry->debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->credit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No ledger entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
