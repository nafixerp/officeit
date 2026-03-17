@extends('layouts.app')
@section('title', 'Employee Loans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Employee Loans</h1>
    <a href="{{ route('employee-loans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Loan</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Loan Type</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Monthly Deduction</th>
                    <th>Start Date</th>
                    <th class="text-end">Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans ?? [] as $index => $loan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $loan->employee->full_name ?? '-' }}</td>
                    <td>{{ $loan->loan_type }}</td>
                    <td class="text-end">{{ number_format($loan->amount, 2) }}</td>
                    <td class="text-end">{{ number_format($loan->monthly_deduction, 2) }}</td>
                    <td>{{ $loan->start_date?->format('d M Y') }}</td>
                    <td class="text-end">{{ number_format($loan->remaining_balance ?? $loan->amount, 2) }}</td>
                    <td>
                        @php $statusColors = ['active' => 'warning', 'completed' => 'success', 'cancelled' => 'secondary']; @endphp
                        <span class="badge bg-{{ $statusColors[$loan->status] ?? 'secondary' }}">{{ ucfirst($loan->status ?? 'active') }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('employee-loans.edit', $loan) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('employee-loans.destroy', $loan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No employee loans found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($loans ?? collect(), 'links'))
    <div class="card-footer">{{ $loans->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
