@extends('layouts.app')
@section('title', 'Bank Reconciliations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bank Reconciliations</h1>
    <a href="{{ route('bank-reconciliations.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Reconciliation</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Bank Account</th>
                    <th>Date</th>
                    <th class="text-end">Statement Balance</th>
                    <th class="text-end">Book Balance</th>
                    <th class="text-end">Difference</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reconciliations ?? [] as $index => $recon)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $recon->bankAccount->name ?? $recon->bankAccount->account_name ?? '-' }}</td>
                    <td>{{ $recon->date?->format('d M Y') }}</td>
                    <td class="text-end">{{ number_format($recon->statement_balance ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($recon->book_balance ?? 0, 2) }}</td>
                    <td class="text-end {{ ($recon->difference ?? 0) != 0 ? 'text-danger fw-bold' : 'text-success' }}">
                        {{ number_format($recon->difference ?? 0, 2) }}
                    </td>
                    <td>
                        @if(($recon->difference ?? 0) == 0)
                            <span class="badge bg-success">Reconciled</span>
                        @else
                            <span class="badge bg-warning">Unreconciled</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('bank-reconciliations.show', $recon) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No reconciliations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
