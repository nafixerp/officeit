@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Chart of Accounts</h1>
    <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

@foreach(['ASSET', 'LIABILITY', 'EQUITY', 'INCOME', 'EXPENSE'] as $type)
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ $type }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Sub Type</th>
                            <th>Group</th>
                            <th class="text-end">Balance</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($accountsByType[$type] ?? collect()) as $account)
                            <tr>
                                <td>
                                    @if($account->parent_id)
                                        <span class="text-muted ms-3">{{ $account->code }}</span>
                                    @else
                                        <strong>{{ $account->code }}</strong>
                                    @endif
                                </td>
                                <td>
                                    @if($account->parent_id)
                                        <span class="ms-3">{{ $account->name }}</span>
                                    @else
                                        <strong>{{ $account->name }}</strong>
                                    @endif
                                </td>
                                <td>{{ $account->sub_type ?? '-' }}</td>
                                <td>
                                    @if($account->is_group)
                                        <span class="badge bg-info">Group</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($account->balance ?? 0, 2) }}</td>
                                <td>
                                    <a href="{{ route('chart-of-accounts.ledger', $account) }}" class="btn btn-info btn-sm">Ledger</a>
                                    <a href="{{ route('chart-of-accounts.edit', $account) }}" class="btn btn-warning btn-sm">Edit</a>
                                    @if(!$account->is_system)
                                    <form action="{{ route('chart-of-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No accounts in this type.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach
@endsection
