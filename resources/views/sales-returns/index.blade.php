@extends('layouts.app')
@section('title', 'Sales Returns')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Sales Returns</h1>
    <a href="{{ route('sales-returns.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Sales Return</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Return #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Invoice #</th>
                    <th class="text-end">Amount</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salesReturns ?? [] as $return)
                <tr>
                    <td>{{ $return->return_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                    <td>{{ $return->customer->name ?? '-' }}</td>
                    <td>{{ $return->invoice->invoice_number ?? '-' }}</td>
                    <td class="text-end">{{ number_format($return->amount, 2) }}</td>
                    <td>{{ Str::limit($return->reason, 30) }}</td>
                    <td><span class="badge {{ $return->status == 'Approved' ? 'bg-success' : ($return->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $return->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('sales-returns.edit', $return) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('sales-returns.destroy', $return) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No sales returns found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($salesReturns ?? collect(), 'links'))
    <div class="card-footer">{{ $salesReturns->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
