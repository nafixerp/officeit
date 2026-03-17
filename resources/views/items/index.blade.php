@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Items</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('items.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, code...">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="item_type">
                    <option value="">All Types</option>
                    @foreach(['GOODS', 'RAW_MATERIAL', 'FINISHED_GOODS', 'CONSUMABLE'] as $type)
                        <option value="{{ $type }}" {{ request('item_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Unit</th>
                        <th class="text-end">Purchase Rate</th>
                        <th class="text-end">Sales Rate</th>
                        <th class="text-end">Stock</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td>{{ $item->item_type ?? '-' }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td class="text-end">{{ number_format($item->purchase_rate ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($item->sales_rate ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($item->minimum_stock ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('items.show', $item) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </div>
</div>
@endsection
