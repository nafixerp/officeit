@extends('layouts.app')
@section('title', 'Create Stock Transaction')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Create Stock Transaction</h1>
    <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('stock-transactions.store') }}" method="POST">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Transaction Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="item_id" class="form-label">Item <span class="text-danger">*</span></label>
                    <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required>
                        <option value="">Select Item</option>
                        @foreach($items ?? [] as $item)
                            <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses ?? [] as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                    <select name="transaction_type" id="transaction_type" class="form-select @error('transaction_type') is-invalid @enderror" required onchange="toggleToWarehouse()">
                        <option value="">Select Type</option>
                        @foreach(['IN','OUT','TRANSFER','ADJUSTMENT','DAMAGE'] as $type)
                            <option value="{{ $type }}" {{ old('transaction_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('transaction_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" step="0.01" min="0" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="rate" class="form-label">Rate</label>
                    <input type="number" name="rate" id="rate" class="form-control @error('rate') is-invalid @enderror" value="{{ old('rate', 0) }}" step="0.01" min="0">
                    @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3" id="toWarehouseField" style="{{ old('transaction_type') == 'TRANSFER' ? '' : 'display:none;' }}">
                    <label for="to_warehouse_id" class="form-label">To Warehouse <span class="text-danger">*</span></label>
                    <select name="to_warehouse_id" id="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror">
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses ?? [] as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control @error('narration') is-invalid @enderror" rows="3">{{ old('narration') }}</textarea>
                    @error('narration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('stock-transactions.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Transaction</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function toggleToWarehouse() {
    var type = document.getElementById('transaction_type').value;
    var field = document.getElementById('toWarehouseField');
    if (type === 'TRANSFER') {
        field.style.display = '';
    } else {
        field.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', function() { toggleToWarehouse(); });
</script>
@endsection
