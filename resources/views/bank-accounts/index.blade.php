@extends('layouts.app')

@section('title', 'Bank Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Bank Accounts</h1>
    <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Bank Name</th>
                        <th>Account #</th>
                        <th>Account Holder</th>
                        <th>Currency</th>
                        <th class="text-end">Opening Balance</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankAccounts as $bankAccount)
                        <tr>
                            <td>{{ $bankAccount->bank_name }}</td>
                            <td>{{ $bankAccount->account_number }}</td>
                            <td>{{ $bankAccount->account_holder_name ?? '-' }}</td>
                            <td>{{ $bankAccount->currency->code ?? '-' }}</td>
                            <td class="text-end">{{ number_format($bankAccount->opening_balance ?? 0, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $bankAccount->is_active ? 'success' : 'secondary' }}">{{ $bankAccount->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('bank-accounts.destroy', $bankAccount) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No bank accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bankAccounts->links() }}
    </div>
</div>
@endsection
