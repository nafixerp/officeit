@extends('layouts.app')
@section('title', 'Assets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Assets</h1>
    <a href="{{ route('assets.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Asset</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Purchase Date</th>
                    <th class="text-end">Value</th>
                    <th class="text-end">Depreciation</th>
                    <th class="text-end">Current Value</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets ?? [] as $asset)
                <tr>
                    <td><code>{{ $asset->code }}</code></td>
                    <td>{{ $asset->name }}</td>
                    <td>{{ $asset->category }}</td>
                    <td>{{ $asset->purchase_date?->format('d M Y') }}</td>
                    <td class="text-end">{{ number_format($asset->purchase_value ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($asset->accumulated_depreciation ?? 0, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($asset->current_value ?? 0, 2) }}</td>
                    <td>
                        @php $statusColors = ['active' => 'success', 'disposed' => 'danger', 'maintenance' => 'warning', 'inactive' => 'secondary']; @endphp
                        <span class="badge bg-{{ $statusColors[$asset->status] ?? 'secondary' }}">{{ ucfirst($asset->status) }}</span>
                    </td>
                    <td>{{ $asset->location ?? '-' }}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('assets.show', $asset) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No assets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($assets ?? collect(), 'links'))
    <div class="card-footer">{{ $assets->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
