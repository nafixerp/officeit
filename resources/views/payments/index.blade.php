@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Payments</h1>
    <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Payment</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Mode</label>
                <select name="payment_mode" class="form-select">
                    <option value="">All Modes</option>
                    <option value="Cash" {{ request('payment_mode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank" {{ request('payment_mode') == 'Bank' ? 'selected' : '' }}>Bank</option>
                    <option value="Cheque" {{ request('payment_mode') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Transfer" {{ request('payment_mode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="Online" {{ request('payment_mode') == 'Online' ? 'selected' : '' }}>Online</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers ?? [] as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
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
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Payment #</th><th>Date</th><th>Paid To</th><th>Mode</th>
                    <th>Currency</th><th class="text-end">Amount</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments ?? [] as $payment)
                <tr>
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                    <td>{{ $payment->supplier->name ?? '-' }}</td>
                    <td>{{ $payment->payment_mode }}</td>
                    <td>{{ $payment->currency }}</td>
                    <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge {{ $payment->status == 'Confirmed' ? 'bg-success' : ($payment->status == 'Draft' ? 'bg-secondary' : 'bg-danger') }}">{{ $payment->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="{{ route('payments.print', $payment) }}" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer"></i></a>
                            <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payments ?? collect(), 'links'))
    <div class="card-footer">{{ $payments->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
