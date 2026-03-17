@extends('layouts.app')
@section('title', 'Asset Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Asset Details: {{ $asset->name }}</h1>
    <div>
        <a href="{{ route('assets.edit', $asset) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('assets.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Asset Information</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:40%">Code</td><td><code>{{ $asset->code }}</code></td></tr>
                    <tr><td class="text-muted">Name</td><td>{{ $asset->name }}</td></tr>
                    <tr><td class="text-muted">Category</td><td>{{ $asset->category }}</td></tr>
                    <tr><td class="text-muted">Purchase Date</td><td>{{ $asset->purchase_date?->format('d M Y') }}</td></tr>
                    <tr><td class="text-muted">Purchase Value</td><td>{{ number_format($asset->purchase_value ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Salvage Value</td><td>{{ number_format($asset->salvage_value ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Location</td><td>{{ $asset->location ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Assigned To</td><td>{{ $asset->assignedEmployee->full_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Status</td><td>
                        @php $statusColors = ['active' => 'success', 'disposed' => 'danger', 'maintenance' => 'warning', 'inactive' => 'secondary']; @endphp
                        <span class="badge bg-{{ $statusColors[$asset->status] ?? 'secondary' }}">{{ ucfirst($asset->status) }}</span>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Depreciation Info</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:40%">Method</td><td>{{ ucfirst(str_replace('_', ' ', $asset->depreciation_method ?? 'straight_line')) }}</td></tr>
                    <tr><td class="text-muted">Useful Life</td><td>{{ $asset->useful_life_years ?? '-' }} years</td></tr>
                    <tr><td class="text-muted">Rate</td><td>{{ $asset->depreciation_rate ?? '-' }}%</td></tr>
                    <tr><td class="text-muted">Accumulated Depreciation</td><td class="text-danger">{{ number_format($asset->accumulated_depreciation ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Current Value</td><td class="fw-bold text-success">{{ number_format($asset->current_value ?? 0, 2) }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

@if($asset->description)
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Description</h5></div>
    <div class="card-body">{{ $asset->description }}</div>
</div>
@endif

<!-- Depreciation Schedule -->
<div class="card">
    <div class="card-header"><h5 class="mb-0">Depreciation Schedule</h5></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Year</th>
                    <th class="text-end">Opening Value</th>
                    <th class="text-end">Depreciation</th>
                    <th class="text-end">Accumulated</th>
                    <th class="text-end">Closing Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($depreciationSchedule ?? [] as $schedule)
                <tr>
                    <td>{{ $schedule['year'] }}</td>
                    <td class="text-end">{{ number_format($schedule['opening_value'], 2) }}</td>
                    <td class="text-end text-danger">{{ number_format($schedule['depreciation'], 2) }}</td>
                    <td class="text-end">{{ number_format($schedule['accumulated'], 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($schedule['closing_value'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No depreciation schedule available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
