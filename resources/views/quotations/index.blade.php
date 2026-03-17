@extends('layouts.app')
@section('title', 'Quotations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Quotations</h1>
    <a href="{{ route('quotations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Quotation
    </a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('quotations.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Converted" {{ request('status') == 'Converted' ? 'selected' : '' }}>Converted</option>
                    <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                    <th>Quotation #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Currency</th>
                    <th class="text-end">Total Amount</th>
                    <th>Status</th>
                    <th>Validity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations ?? [] as $quotation)
                <tr>
                    <td>{{ $quotation->quotation_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($quotation->date)->format('d M Y') }}</td>
                    <td>{{ $quotation->customer->name ?? '-' }}</td>
                    <td>{{ $quotation->currency }}</td>
                    <td class="text-end">{{ number_format($quotation->total_amount, 2) }}</td>
                    <td>
                        @php
                            $badgeClass = match($quotation->status) {
                                'Draft' => 'bg-secondary',
                                'Sent' => 'bg-info',
                                'Approved' => 'bg-success',
                                'Rejected' => 'bg-danger',
                                'Converted' => 'bg-primary',
                                'Expired' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $quotation->status }}</span>
                    </td>
                    <td>{{ $quotation->validity_date ? \Carbon\Carbon::parse($quotation->validity_date)->format('d M Y') : '-' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            @if($quotation->status === 'Approved')
                            <a href="{{ route('quotations.convert', $quotation) }}" class="btn btn-outline-success" title="Convert to Order"><i class="bi bi-arrow-right-circle"></i></a>
                            @endif
                            <form action="{{ route('quotations.destroy', $quotation) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this quotation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No quotations found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($quotations ?? collect(), 'links'))
    <div class="card-footer">
        {{ $quotations->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
