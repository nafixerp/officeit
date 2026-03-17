@extends('layouts.app')

@section('title', 'Currencies')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Currencies</h1>
    <a href="{{ route('currencies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Decimal Places</th>
                        <th>Base Currency</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                        <tr>
                            <td>{{ $currency->code }}</td>
                            <td>{{ $currency->name }}</td>
                            <td>{{ $currency->symbol }}</td>
                            <td>{{ $currency->decimal_places }}</td>
                            <td>
                                @if($currency->is_base_currency)
                                    <span class="badge bg-primary">Base Currency</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('currencies.edit', $currency) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('currencies.destroy', $currency) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No currencies found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $currencies->links() }}
    </div>
</div>
@endsection
