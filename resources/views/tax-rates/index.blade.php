@extends('layouts.app')

@section('title', 'Tax Rates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Tax Rates</h1>
    <a href="{{ route('tax-rates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Rate %</th>
                        <th>Type</th>
                        <th>Tax Profile</th>
                        <th>Default</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxRates as $rate)
                        <tr>
                            <td>{{ $rate->name }}</td>
                            <td>{{ $rate->code }}</td>
                            <td>{{ $rate->rate }}%</td>
                            <td>{{ $rate->type ?? '-' }}</td>
                            <td>{{ $rate->taxProfile->name ?? '-' }}</td>
                            <td>
                                @if($rate->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('tax-rates.edit', $rate) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('tax-rates.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No tax rates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $taxRates->links() }}
    </div>
</div>
@endsection
