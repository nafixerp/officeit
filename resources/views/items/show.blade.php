@extends('layouts.app')

@section('title', 'Item Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Item: {{ $item->name }}</h1>
    <div>
        <a href="{{ route('items.edit', $item) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Item Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2"><strong>Code:</strong><br>{{ $item->code }}</div>
                    <div class="col-md-4 mb-2"><strong>Barcode:</strong><br>{{ $item->barcode ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Name:</strong><br>{{ $item->name }}</div>
                    <div class="col-md-4 mb-2"><strong>Category:</strong><br>{{ $item->category->name ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Type:</strong><br>{{ $item->item_type ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Unit:</strong><br>{{ $item->unit ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Brand:</strong><br>{{ $item->brand ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Warehouse:</strong><br>{{ $item->warehouse->name ?? '-' }}</div>
                    <div class="col-md-4 mb-2"><strong>Tax Rate:</strong><br>{{ $item->taxRate->name ?? '-' }} {{ $item->taxRate ? '(' . $item->taxRate->rate . '%)' : '' }}</div>
                    <div class="col-12 mb-2"><strong>Description:</strong><br>{{ $item->description ?? '-' }}</div>
                    <div class="col-md-4 mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Pricing & Stock</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Purchase Rate:</strong> {{ number_format($item->purchase_rate ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Sales Rate:</strong> {{ number_format($item->sales_rate ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Minimum Stock:</strong> {{ number_format($item->minimum_stock ?? 0, 2) }}</div>
                <div class="mb-2"><strong>Reorder Level:</strong> {{ number_format($item->reorder_level ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Linked Accounts</h5></div>
            <div class="card-body">
                <div class="mb-2"><strong>Purchase:</strong> {{ $item->purchaseAccount->name ?? '-' }}</div>
                <div class="mb-2"><strong>Sales:</strong> {{ $item->salesAccount->name ?? '-' }}</div>
                <div class="mb-2"><strong>Inventory:</strong> {{ $item->inventoryAccount->name ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

@if(isset($stockMovements) && $stockMovements->count())
<div class="card">
    <div class="card-header"><h5 class="mb-0">Stock Movement History</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Reference</th><th class="text-end">Qty In</th><th class="text-end">Qty Out</th><th class="text-end">Balance</th></tr>
                </thead>
                <tbody>
                    @foreach($stockMovements as $movement)
                        <tr>
                            <td>{{ $movement->date->format('Y-m-d') }}</td>
                            <td>{{ $movement->type }}</td>
                            <td>{{ $movement->reference }}</td>
                            <td class="text-end">{{ number_format($movement->qty_in, 2) }}</td>
                            <td class="text-end">{{ number_format($movement->qty_out, 2) }}</td>
                            <td class="text-end">{{ number_format($movement->balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
