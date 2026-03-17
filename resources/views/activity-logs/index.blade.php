@extends('layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Activity Logs</h1>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs ?? [] as $log)
                <tr>
                    <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    <td>{{ $log->user->name ?? '-' }}</td>
                    <td>
                        @php $actionColors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger', 'login' => 'info', 'logout' => 'secondary']; @endphp
                        <span class="badge bg-{{ $actionColors[$log->action] ?? 'secondary' }}">{{ ucfirst($log->action) }}</span>
                    </td>
                    <td>{{ $log->module ?? '-' }}</td>
                    <td>{{ $log->description ?? '-' }}</td>
                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No activity logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($logs ?? collect(), 'links'))
    <div class="card-footer">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
