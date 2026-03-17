@extends('layouts.app')
@section('title', 'Leave Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Leave Requests</h1>
    <a href="{{ route('leave-requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Request</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests ?? [] as $request)
                <tr>
                    <td>{{ $request->employee->full_name ?? '-' }}</td>
                    <td>{{ $request->leaveType->name ?? '-' }}</td>
                    <td>{{ $request->start_date?->format('d M Y') }}</td>
                    <td>{{ $request->end_date?->format('d M Y') }}</td>
                    <td>{{ $request->days }}</td>
                    <td>{{ Str::limit($request->reason, 30) }}</td>
                    <td>
                        @php $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; @endphp
                        <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">{{ ucfirst($request->status) }}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if($request->status === 'pending')
                                <form action="{{ route('leave-requests.approve', $request) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <form action="{{ route('leave-requests.reject', $request) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                </form>
                            @endif
                            <a href="{{ route('leave-requests.edit', $request) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('leave-requests.destroy', $request) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No leave requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($leaveRequests ?? collect(), 'links'))
    <div class="card-footer">{{ $leaveRequests->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
