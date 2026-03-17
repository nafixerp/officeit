@extends('layouts.app')

@section('title', 'Exchange Rates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Exchange Rates</h1>
    <a href="{{ route('exchange-rates.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>From Currency</th>
                        <th>To Currency</th>
                        <th>Rate</th>
                        <th>Effective Date</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exchangeRates as $rate)
                        <tr>
                            <td>{{ $rate->fromCurrency->code ?? '-' }}</td>
                            <td>{{ $rate->toCurrency->code ?? '-' }}</td>
                            <td>{{ $rate->rate }}</td>
                            <td>{{ $rate->effective_date->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('exchange-rates.edit', $rate) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('exchange-rates.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No exchange rates found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exchangeRates->links() }}
    </div>
</div>
@endsection
