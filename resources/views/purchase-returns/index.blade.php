@extends('layouts.app')
@section('title', 'Purchase Returns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Purchase Returns</h1>
    <a href="{{ route('purchase-returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Purchase Return</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Return #</th><th>Date</th><th>Supplier</th><th>Invoice #</th>
                    <th class="text-end">Amount</th><th>Reason</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseReturns ?? [] as $return)
                <tr>
                    <td>{{ $return->return_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                    <td>{{ $return->supplier->name ?? '-' }}</td>
                    <td>{{ $return->invoice->invoice_number ?? '-' }}</td>
                    <td class="text-end">{{ number_format($return->amount, 2) }}</td>
                    <td>{{ Str::limit($return->reason, 30) }}</td>
                    <td><span class="badge {{ $return->status == 'Approved' ? 'bg-success' : ($return->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $return->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('purchase-returns.edit', $return) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('purchase-returns.destroy', $return) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No purchase returns found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($purchaseReturns ?? collect(), 'links'))
    <div class="card-footer">{{ $purchaseReturns->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
