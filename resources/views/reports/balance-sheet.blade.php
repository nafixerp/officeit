@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Balance Sheet</h1>
    <button onclick="window.print()" class="btn btn-outline-primary no-print"><i class="bi bi-printer"></i> Print</button>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ url('/reports/balance-sheet') }}" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">As of Date</label>
                <input type="date" name="as_of_date" class="form-control" value="{{ request('as_of_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Generate</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <!-- Assets -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white"><h5 class="mb-0">Assets</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @php $totalAssets = 0; @endphp
                        @foreach($assetAccounts ?? [] as $account)
                        @php $totalAssets += $account->balance ?? 0; @endphp
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td class="text-end" style="width:150px;">{{ number_format($account->balance ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-primary">
                        <tr>
                            <td class="fw-bold">Total Assets</td>
                            <td class="text-end fw-bold">{{ number_format($totalAssets, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Liabilities -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white"><h5 class="mb-0">Liabilities</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @php $totalLiabilities = 0; @endphp
                        @foreach($liabilityAccounts ?? [] as $account)
                        @php $totalLiabilities += $account->balance ?? 0; @endphp
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td class="text-end" style="width:150px;">{{ number_format($account->balance ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-danger">
                        <tr>
                            <td class="fw-bold">Total Liabilities</td>
                            <td class="text-end fw-bold">{{ number_format($totalLiabilities, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Equity -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white"><h5 class="mb-0">Equity</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <tbody>
                        @php $totalEquity = 0; @endphp
                        @foreach($equityAccounts ?? [] as $account)
                        @php $totalEquity += $account->balance ?? 0; @endphp
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td class="text-end" style="width:150px;">{{ number_format($account->balance ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-success">
                        <tr>
                            <td class="fw-bold">Total Equity</td>
                            <td class="text-end fw-bold">{{ number_format($totalEquity, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr class="fw-bold"><td>Total Liabilities + Equity</td><td class="text-end">{{ number_format($totalLiabilities + $totalEquity, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
