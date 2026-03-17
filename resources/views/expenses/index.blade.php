@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Expenses</h1>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Expense</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
            <div class="col-md-2">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branches ?? [] as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary me-2">Filter</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Date</th><th>Category</th><th>Description</th>
                    <th class="text-end">Amount</th><th class="text-end">Tax</th><th class="text-end">Total</th>
                    <th>Payment Mode</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses ?? [] as $expense)
                <tr>
                    <td>{{ $expense->expense_number ?? $expense->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
                    <td>{{ $expense->category->name ?? '-' }}</td>
                    <td>{{ Str::limit($expense->description, 30) }}</td>
                    <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($expense->tax_amount, 2) }}</td>
                    <td class="text-end">{{ number_format($expense->total_amount, 2) }}</td>
                    <td>{{ $expense->payment_mode }}</td>
                    <td><span class="badge {{ $expense->status == 'Approved' ? 'bg-success' : ($expense->status == 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $expense->status }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($expenses ?? collect(), 'links'))
    <div class="card-footer">{{ $expenses->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
