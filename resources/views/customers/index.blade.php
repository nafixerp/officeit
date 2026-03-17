@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Customers</h1>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('customers.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, code, email...">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="customer_type">
                    <option value="">All Types</option>
                    @foreach(['INDIVIDUAL', 'COMPANY', 'GOVERNMENT'] as $type)
                        <option value="{{ $type }}" {{ request('customer_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="country_id">
                    <option value="">All Countries</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ request('country_id') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Company</th>
                        <th>Type</th>
                        <th>Country</th>
                        <th>Phone</th>
                        <th>Credit Limit</th>
                        <th>Balance</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->code }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->company_name ?? '-' }}</td>
                            <td>{{ $customer->customer_type ?? '-' }}</td>
                            <td>{{ $customer->country->name ?? '-' }}</td>
                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ number_format($customer->credit_limit ?? 0, 2) }}</td>
                            <td>{{ number_format($customer->opening_balance ?? 0, 2) }}</td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-info btn-sm">View</a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
