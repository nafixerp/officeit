@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        {{-- Today Sales --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Today Sales</div>
                        <div class="stat-value">{{ number_format($todaySales ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today Purchases --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Today Purchases</div>
                        <div class="stat-value">{{ number_format($todayPurchases ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today Receipts --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Today Receipts</div>
                        <div class="stat-value">{{ number_format($todayReceipts ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today Payments --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Today Payments</div>
                        <div class="stat-value">{{ number_format($todayPayments ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cash Balance --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Cash Balance</div>
                        <div class="stat-value">{{ number_format($cashBalance ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bank Balance --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-university"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Bank Balance</div>
                        <div class="stat-value">{{ number_format($bankBalance ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding Receivables --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Outstanding Receivables</div>
                        <div class="stat-value">{{ number_format($outstandingReceivables ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Outstanding Payables --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stat-label">Outstanding Payables</div>
                        <div class="stat-value">{{ number_format($outstandingPayables ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Sales Overview</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Purchase Overview</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="purchase-chart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Receipts vs Payments</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Last 12 Months</span>
                </div>
                <div class="card-body">
                    <canvas id="receipt-payment-chart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Expenses Breakdown</h6>
                    <span class="text-muted" style="font-size: 0.75rem;">Current Month</span>
                </div>
                <div class="card-body">
                    <canvas id="expense-chart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section --}}
    <div class="row g-3">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Recent Transactions --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Recent Transactions</h6>
                    <a href="#" class="text-decoration-none" style="font-size: 0.8rem;">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="padding-left: 1.25rem;">Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Party</th>
                                    <th class="text-end" style="padding-right: 1.25rem;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($recentTransactions ?? []) as $txn)
                                    <tr>
                                        <td style="padding-left: 1.25rem;">{{ $txn->date ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $txn->type ?? '-' }}</span>
                                        </td>
                                        <td>{{ $txn->reference ?? '-' }}</td>
                                        <td>{{ $txn->party ?? '-' }}</td>
                                        <td class="text-end fw-medium" style="padding-right: 1.25rem;">
                                            {{ number_format($txn->amount ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox me-2"></i>No recent transactions
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pending Approvals --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">Pending Approvals</h6>
                    <span class="badge bg-warning text-dark">{{ count($pendingApprovals ?? []) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse(($pendingApprovals ?? []) as $approval)
                            <a href="{{ $approval->url ?? '#' }}" class="list-group-item list-group-item-action d-flex align-items-center py-3 px-4">
                                <div class="flex-grow-1">
                                    <div class="fw-medium" style="font-size: 0.85rem;">{{ $approval->title ?? '-' }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">{{ $approval->description ?? '' }}</div>
                                </div>
                                <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                            </a>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle me-2"></i>No pending approvals
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Top 5 Customers --}}
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="mb-0">Top 5 Customers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse(($topCustomers ?? []) as $customer)
                            <div class="list-group-item d-flex align-items-center py-3 px-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.8rem; font-weight: 600;">
                                        {{ substr($customer->name ?? 'C', 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium" style="font-size: 0.85rem;">{{ $customer->name ?? '-' }}</div>
                                </div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">
                                    {{ number_format($customer->total ?? 0, 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4" style="font-size: 0.85rem;">
                                <i class="fas fa-users me-1"></i> No data available
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Top 5 Suppliers --}}
            <div class="card mb-3">
                <div class="card-header py-3">
                    <h6 class="mb-0">Top 5 Suppliers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse(($topSuppliers ?? []) as $supplier)
                            <div class="list-group-item d-flex align-items-center py-3 px-4">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.8rem; font-weight: 600;">
                                        {{ substr($supplier->name ?? 'S', 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium" style="font-size: 0.85rem;">{{ $supplier->name ?? '-' }}</div>
                                </div>
                                <div class="fw-semibold" style="font-size: 0.85rem;">
                                    {{ number_format($supplier->total ?? 0, 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4" style="font-size: 0.85rem;">
                                <i class="fas fa-truck me-1"></i> No data available
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0">Quick Links</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ url('/sales-invoices/create') }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Sales Invoice
                        </a>
                        <a href="{{ url('/purchase-invoices/create') }}" class="btn btn-outline-warning btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Purchase Invoice
                        </a>
                        <a href="{{ url('/receipts/create') }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Receipt
                        </a>
                        <a href="{{ url('/payments/create') }}" class="btn btn-outline-danger btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Payment
                        </a>
                        <a href="{{ url('/journal-entries/create') }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Journal Entry
                        </a>
                        <a href="{{ url('/employees/create') }}" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fas fa-plus me-2"></i> New Employee
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 0.15rem;
        }
    </style>
@endsection

@section('scripts')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8' },
                        beginAtZero: true
                    }
                }
            };

            // Sales Chart
            new Chart(document.getElementById('sales-chart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Sales',
                        data: {!! json_encode($salesChartData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderRadius: 5,
                        barThickness: 18
                    }]
                },
                options: chartDefaults
            });

            // Purchase Chart
            new Chart(document.getElementById('purchase-chart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Purchases',
                        data: {!! json_encode($purchaseChartData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderRadius: 5,
                        barThickness: 18
                    }]
                },
                options: chartDefaults
            });

            // Receipt vs Payment Chart
            new Chart(document.getElementById('receipt-payment-chart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Receipts',
                            data: {!! json_encode($receiptChartData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        },
                        {
                            label: 'Payments',
                            data: {!! json_encode($paymentChartData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    ...chartDefaults,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { font: { size: 11, family: 'Inter' }, boxWidth: 12, padding: 16 }
                        }
                    }
                }
            });

            // Expense Chart (Doughnut)
            new Chart(document.getElementById('expense-chart'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($expenseLabels ?? ['Rent', 'Salaries', 'Utilities', 'Office', 'Other']) !!},
                    datasets: [{
                        data: {!! json_encode($expenseChartData ?? [0, 0, 0, 0, 0]) !!},
                        backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#64748b'],
                        borderWidth: 0,
                        cutout: '65%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 11, family: 'Inter' }, boxWidth: 12, padding: 12 }
                        }
                    }
                }
            });
        });
    </script>
@endsection
