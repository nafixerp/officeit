@extends('layouts.app')
@section('title', 'Profit & Loss')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Profit & Loss Statement</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/profit-and-loss') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Income Section -->
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Income</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @php $totalIncome = 0; @endphp
                        @foreach($incomeAccounts ?? [] as $account)
                        @php $totalIncome += $account->balance ?? 0; @endphp
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td class="text-end" style="width:200px;">{{ number_format($account->balance ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-success">
                        <tr>
                            <td class="fw-bold">Total Income</td>
                            <td class="text-end fw-bold">{{ number_format($totalIncome, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Expense Section -->
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white"><h5 class="mb-0">Expenses</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @php $totalExpenses = 0; @endphp
                        @foreach($expenseAccounts ?? [] as $account)
                        @php $totalExpenses += $account->balance ?? 0; @endphp
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td class="text-end" style="width:200px;">{{ number_format($account->balance ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <td class="fw-bold">Total Expenses</td>
                            <td class="text-end fw-bold">{{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Net Profit/Loss -->
<div class="card {{ ($totalIncome - $totalExpenses) >= 0 ? 'border-success' : 'border-danger' }}">
    <div class="card-body text-center">
        <h3>Net {{ ($totalIncome - $totalExpenses) >= 0 ? 'Profit' : 'Loss' }}:
            <span class="{{ ($totalIncome - $totalExpenses) >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format(abs($totalIncome - $totalExpenses), 2) }}
            </span>
        </h3>
    </div>
</div>
@endsection
