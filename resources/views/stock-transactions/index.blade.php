@extends('layouts.app')
@section('title', 'Stock Transactions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Stock Transactions</h1>
    <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Transaction
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('stock-transactions.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="transaction_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach(['IN','OUT','TRANSFER','ADJUSTMENT','DAMAGE'] as $type)
                        <option value="{{ $type }}" {{ request('transaction_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Item</label>
                <select name="item_id" class="form-select">
                    <option value="">All Items</option>
                    @foreach($items ?? [] as $item)
                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Value</th>
                    <th>Warehouse</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                    <td>{{ $txn->item->name ?? '-' }}</td>
                    <td>
                        @php
                            $typeClass = match($txn->transaction_type) {
                                'IN' => 'bg-success', 'OUT' => 'bg-danger', 'TRANSFER' => 'bg-info',
                                'ADJUSTMENT' => 'bg-warning text-dark', 'DAMAGE' => 'bg-dark', default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $typeClass }}">{{ $txn->transaction_type }}</span>
                    </td>
                    <td class="text-end">{{ number_format($txn->quantity, 2) }}</td>
                    <td class="text-end">{{ number_format($txn->rate, 2) }}</td>
                    <td class="text-end">{{ number_format($txn->quantity * $txn->rate, 2) }}</td>
                    <td>{{ $txn->warehouse->name ?? '-' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('stock-transactions.show', $txn) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('stock-transactions.edit', $txn) }}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            <form action="{{ route('stock-transactions.destroy', $txn) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transaction?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No stock transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($transactions ?? collect(), 'links'))
    <div class="card-footer">{{ $transactions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
