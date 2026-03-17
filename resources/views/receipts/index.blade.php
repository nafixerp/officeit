@extends('layouts.app')
@section('title', 'Receipts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Receipts</h1>
    <a href="{{ route('receipts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Receipt</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('receipts.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Mode</label>
                <select name="receipt_mode" class="form-select">
                    <option value="">All Modes</option>
                    <option value="Cash" {{ request('receipt_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ request('receipt_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                    <option value="Cheque" {{ request('receipt_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Transfer" {{ request('receipt_mode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="Online" {{ request('receipt_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                </select>
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
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt #</th><th>Date</th><th>Received From</th><th>Mode</th>
                    <th>Currency</th><th class="text-end">Amount</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts ?? [] as $receipt)
                <tr>
                    <td>{{ $receipt->receipt_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</td>
                    <td>{{ $receipt->customer->name ?? '-' }}</td>
                    <td>{{ $receipt->receipt_mode }}</td>
                    <td>{{ $receipt->currency }}</td>
                    <td class="text-end">{{ number_format($receipt->amount, 2) }}</td>
                    <td><span class="badge {{ $receipt->status == 'Confirmed' ? 'bg-success' : ($receipt->status == 'Draft' ? 'bg-secondary' : 'bg-danger') }}">{{ $receipt->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('receipts.edit', $receipt) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('receipts.print', $receipt) }}" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer"></i></a>
                            <form action="{{ route('receipts.destroy', $receipt) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No receipts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($receipts ?? collect(), 'links'))
    <div class="card-footer">{{ $receipts->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
