@extends('layouts.app')
@section('title', 'Edit Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Permissions: {{ ucfirst(str_replace('_', ' ', $role)) }}</h1>
    <a href="{{ route('permissions.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form action="{{ route('permissions.update', $role) }}" method="POST">
    @csrf @method('PUT')

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Module</th>
                        <th class="text-center">View</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">Edit</th>
                        <th class="text-center">Delete</th>
                        <th class="text-center">
                            <div class="form-check d-inline">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll(this)">
                                <label class="form-check-label" for="selectAll">All</label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules ?? ['Sales', 'Purchase', 'Accounting', 'HR', 'Payroll', 'Inventory', 'Reports', 'Settings', 'Users'] as $module)
                    @php $perms = $permissions[$module] ?? []; @endphp
                    <tr>
                        <td class="fw-bold">{{ $module }}</td>
                        @foreach(['view', 'create', 'edit', 'delete'] as $action)
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox"
                                    name="permissions[{{ $module }}][]"
                                    value="{{ $action }}"
                                    class="form-check-input perm-checkbox perm-{{ $module }}"
                                    {{ in_array($action, $perms) ? 'checked' : '' }}>
                            </div>
                        </td>
                        @endforeach
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox" class="form-check-input" onchange="toggleRow('{{ $module }}', this)">
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Permissions</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleAll(el) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = el.checked);
}
function toggleRow(module, el) {
    document.querySelectorAll('.perm-' + module).forEach(cb => cb.checked = el.checked);
}
</script>
@endpush
