@extends('layouts.app')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Payments</h1>
    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Payment
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option value="">All Modes</option>
                    @foreach(['Cash','Bank','Cheque','Transfer','Online'] as $mode)
                        <option value="{{ $mode }}" {{ request('payment_mode') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(['Draft','Confirmed','Cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                    <th>Payment #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Mode</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments ?? [] as $payment)
                <tr>
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                    <td>{{ $payment->supplier->name ?? '-' }}</td>
                    <td>{{ $payment->payment_mode }}</td>
                    <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                    <td>
                        @php
                            $badgeClass = match($payment->status) {
                                'Draft' => 'bg-secondary',
                                'Confirmed' => 'bg-success',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $payment->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                            <form action="{{ route('payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($payments ?? collect(), 'links'))
    <div class="card-footer">{{ $payments->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
