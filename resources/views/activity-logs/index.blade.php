@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Activity Logs</h1>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ url('/activity-logs') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select class="form-select" name="user_id">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
                <a href="{{ url('/activity-logs') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                        <tr>
                            <td>{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}</td>
                            <td>{{ $log->user->name ?? $log->causer->name ?? '-' }}</td>
                            <td>
                                @php
                                    $actionColors = ['create' => 'success', 'update' => 'warning', 'delete' => 'danger', 'login' => 'info', 'logout' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $actionColors[$log->action ?? ''] ?? 'primary' }}">{{ ucfirst($log->action ?? $log->event ?? '-') }}</span>
                            </td>
                            <td>{{ $log->module ?? $log->log_name ?? '-' }}</td>
                            <td>{{ $log->description ?? '-' }}</td>
                            <td>{{ $log->ip_address ?? $log->properties['ip'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No activity logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($logs ?? collect(), 'links'))
            {{ $logs->links() }}
        @endif
    </div>
</div>
@endsection
