@extends('layouts.app')
@section('title', 'Quotation #' . $quotation->quotation_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Quotation #{{ $quotation->quotation_number }}</h1>
    <div class="btn-group">
        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Back to List</a>
        <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-primary">Edit</a>
    </div>
</div>

{{-- Details Card --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Quotation Details</h5>
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
        <span class="badge {{ $badgeClass }} fs-6">{{ $quotation->status }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="mb-1 text-muted">Customer</p>
                <p class="fw-bold">{{ $quotation->customer->name ?? '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Date</p>
                <p class="fw-bold">{{ \Carbon\Carbon::parse($quotation->date)->format('d M Y') }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Valid Until</p>
                <p class="fw-bold">{{ $quotation->validity_date ? \Carbon\Carbon::parse($quotation->validity_date)->format('d M Y') : '-' }}</p>
            </div>
            <div class="col-md-2">
                <p class="mb-1 text-muted">Currency</p>
                <p class="fw-bold">{{ $quotation->currency }} (Rate: {{ $quotation->exchange_rate }})</p>
            </div>
            <div class="col-md-3">
                <p class="mb-1 text-muted">Branch</p>
                <p class="fw-bold">{{ $quotation->branch->name ?? '-' }}</p>
            </div>
        </div>
        @if($quotation->remarks)
        <div class="row mt-2">
            <div class="col-12">
                <p class="mb-1 text-muted">Remarks</p>
                <p>{{ $quotation->remarks }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Items Table --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Line Items</h5></div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item / Service</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $item->item->name ?? '-' }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($item->net_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Totals --}}
<div class="row justify-content-end mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">{{ number_format($quotation->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Tax:</td>
                        <td class="text-end">{{ number_format($quotation->total_tax, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Discount:</td>
                        <td class="text-end">{{ number_format($quotation->total_discount, 2) }}</td>
                    </tr>
                    <tr class="table-primary fw-bold">
                        <td>Grand Total:</td>
                        <td class="text-end">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="card">
    <div class="card-body d-flex gap-2 flex-wrap">
        @if($quotation->status === 'Draft' || $quotation->status === 'Sent')
        <form action="{{ route('quotations.approve', $quotation) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Approve</button>
        </form>
        <form action="{{ route('quotations.reject', $quotation) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle"></i> Reject</button>
        </form>
        @endif
        @if($quotation->status === 'Approved')
        <a href="{{ route('quotations.convert', $quotation) }}" class="btn btn-primary"><i class="bi bi-arrow-right-circle"></i> Convert to Sales Order</a>
        @endif
        <a href="{{ route('quotations.pdf', $quotation) }}" class="btn btn-outline-dark" target="_blank"><i class="bi bi-printer"></i> Print PDF</a>
        <a href="{{ route('quotations.email', $quotation) }}" class="btn btn-outline-info"><i class="bi bi-envelope"></i> Email</a>
    </div>
</div>
@endsection
