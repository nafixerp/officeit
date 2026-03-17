@extends('layouts.app')
@section('title', 'Create Stock Transaction')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Create Stock Transaction</h1>
    <a href="{{ route('stock-transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('stock-transactions.store') }}" method="POST" id="stockForm">
    @csrf
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Transaction Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="item_id" class="form-label">Item <span class="text-danger">*</span></label>
                    <select name="item_id" id="item_id" class="form-select" required>
                        <option value="">Select Item</option>
                        @foreach($items ?? [] as $item)
                            <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                        <option value="">Select Warehouse</option>
                        @foreach($warehouses ?? [] as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type" class="form-select" required onchange="toggleTransferField()">
                        <option value="IN" {{ old('type') == 'IN' ? 'selected' : '' }}>IN</option>
                        <option value="OUT" {{ old('type') == 'OUT' ? 'selected' : '' }}>OUT</option>
                        <option value="TRANSFER" {{ old('type') == 'TRANSFER' ? 'selected' : '' }}>TRANSFER</option>
                        <option value="ADJUSTMENT" {{ old('type') == 'ADJUSTMENT' ? 'selected' : '' }}>ADJUSTMENT</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}" step="any" min="0.01" required>
                </div>
                <div class="col-md-2">
                    <label for="rate" class="form-label">Rate</label>
                    <input type="number" name="rate" id="rate" class="form-control" value="{{ old('rate', 0) }}" step="0.01" min="0">
                </div>
                <div class="col-md-4" id="toWarehouseField" style="display:none;">
                    <label for="to_warehouse_id" class="form-label">To Warehouse</label>
                    <select name="to_warehouse_id" id="to_warehouse_id" class="form-select">
                        <option value="">Select Destination Warehouse</option>
                        @foreach($warehouses ?? [] as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="narration" class="form-label">Narration</label>
                    <textarea name="narration" id="narration" class="form-control" rows="1">{{ old('narration') }}</textarea>
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

@push('scripts')
<script>
function toggleTransferField() {
    const type = document.getElementById('type').value;
    document.getElementById('toWarehouseField').style.display = type === 'TRANSFER' ? 'block' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleTransferField);
</script>
@endpush
