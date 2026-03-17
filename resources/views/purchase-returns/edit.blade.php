@extends('layouts.app')
@section('title', 'Edit Purchase Return')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Edit Purchase Return #{{ $purchaseReturn->return_number }}</h1>
    <a href="{{ route('purchase-returns.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form action="{{ route('purchase-returns.update', $purchaseReturn) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Return Details</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-select" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers ?? [] as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchaseReturn->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="invoice_id" class="form-label">Purchase Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="invoice_id" class="form-select" required>
                        <option value="">Select Invoice</option>
                        @foreach($invoices ?? [] as $inv)
                            <option value="{{ $inv->id }}" {{ old('invoice_id', $purchaseReturn->invoice_id) == $inv->id ? 'selected' : '' }}>#{{ $inv->invoice_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $purchaseReturn->date) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', $purchaseReturn->amount) }}" step="0.01" min="0" required>
                </div>
                <div class="col-md-9">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea name="reason" id="reason" class="form-control" rows="2">{{ old('reason', $purchaseReturn->reason) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Purchase Return</button>
    </div>
</form>
@endsection
