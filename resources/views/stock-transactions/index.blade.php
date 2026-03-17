@extends('layouts.app')
@section('title', 'Stock Transactions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Stock Transactions</h1>
    <a href="{{ route('stock-transactions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Transaction</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th><th>Item</th><th>Type</th>
                    <th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Value</th>
                    <th>Warehouse</th><th>Reference</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $txn)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                    <td>{{ $txn->item->name ?? '-' }}</td>
                    <td>
                        @php
                            $typeBadge = match($txn->type) {
                                'IN' => 'bg-success', 'OUT' => 'bg-danger',
                                'TRANSFER' => 'bg-info', 'ADJUSTMENT' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $typeBadge }}">{{ $txn->type }}</span>
                    </td>
                    <td class="text-end">{{ $txn->quantity }}</td>
                    <td class="text-end">{{ number_format($txn->rate, 2) }}</td>
                    <td class="text-end">{{ number_format($txn->quantity * $txn->rate, 2) }}</td>
                    <td>{{ $txn->warehouse->name ?? '-' }}</td>
                    <td>{{ $txn->reference ?? '-' }}</td>
                    <td>
                        <form action="{{ route('stock-transactions.destroy', $txn) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No stock transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($transactions ?? collect(), 'links'))
    <div class="card-footer">{{ $transactions->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
