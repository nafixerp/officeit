@extends('layouts.app')
@section('title', 'Employee Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Employee Details</h1>
    <div>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<!-- Employee Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 text-center">
                @if($employee->photo)
                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->full_name }}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
                @else
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width:120px;height:120px;font-size:3rem;">
                        {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="col-md-5">
                <h4>{{ $employee->full_name }}</h4>
                <p class="text-muted mb-1">{{ $employee->employee_id_number }}</p>
                <p class="mb-1"><strong>Department:</strong> {{ $employee->department->name ?? '-' }}</p>
                <p class="mb-1"><strong>Designation:</strong> {{ $employee->designation->name ?? '-' }}</p>
                <p class="mb-1"><strong>Branch:</strong> {{ $employee->branch->name ?? '-' }}</p>
            </div>
            <div class="col-md-5">
                <p class="mb-1"><strong>Email:</strong> {{ $employee->email ?? '-' }}</p>
                <p class="mb-1"><strong>Mobile:</strong> {{ $employee->mobile ?? '-' }}</p>
                <p class="mb-1"><strong>Joining Date:</strong> {{ $employee->joining_date?->format('d M Y') ?? '-' }}</p>
                <p class="mb-1"><strong>Status:</strong>
                    @php $statusColors = ['active' => 'success', 'inactive' => 'secondary', 'terminated' => 'danger', 'resigned' => 'warning']; @endphp
                    <span class="badge bg-{{ $statusColors[$employee->status] ?? 'secondary' }}">{{ ucfirst($employee->status) }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs" id="employeeTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="details-tab" data-bs-toggle="tab" href="#details" role="tab">Details</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="documents-tab" data-bs-toggle="tab" href="#documents" role="tab">Documents</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="leave-tab" data-bs-toggle="tab" href="#leave" role="tab">Leave Balance</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="salary-tab" data-bs-toggle="tab" href="#salary" role="tab">Salary Structure</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="attendance-tab" data-bs-toggle="tab" href="#attendance" role="tab">Attendance Summary</a>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-4" id="employeeTabContent">
    <!-- Details Tab -->
    <div class="tab-pane fade show active" id="details" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Personal Information</h5>
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:40%">Gender</td><td>{{ ucfirst($employee->gender ?? '-') }}</td></tr>
                    <tr><td class="text-muted">Nationality</td><td>{{ $employee->nationality ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Passport Number</td><td>{{ $employee->passport_number ?? '-' }}</td></tr>
                    <tr><td class="text-muted">National ID</td><td>{{ $employee->national_id ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Visa Details</td><td>{{ $employee->visa_details ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Address</td><td>{{ $employee->address ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Country</td><td>{{ $employee->country->name ?? '-' }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Bank Details</h5>
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:40%">Basic Salary</td><td>{{ number_format($employee->basic_salary ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Bank Name</td><td>{{ $employee->bank_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Account Number</td><td>{{ $employee->bank_account_number ?? '-' }}</td></tr>
                    <tr><td class="text-muted">IBAN</td><td>{{ $employee->bank_iban ?? '-' }}</td></tr>
                </table>

                <h5 class="mb-3 mt-4">Emergency Contact</h5>
                <table class="table table-borderless">
                    <tr><td class="text-muted" style="width:40%">Name</td><td>{{ $employee->emergency_contact_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">Phone</td><td>{{ $employee->emergency_contact_phone ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Documents Tab -->
    <div class="tab-pane fade" id="documents" role="tabpanel">
        <form action="{{ route('employees.documents.store', $employee) }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Document Type</label>
                    <select name="type" class="form-select">
                        <option value="contract">Contract</option>
                        <option value="id">ID Copy</option>
                        <option value="passport">Passport Copy</option>
                        <option value="visa">Visa Copy</option>
                        <option value="certificate">Certificate</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">File</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload"></i> Upload</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->documents ?? [] as $document)
                <tr>
                    <td>{{ $document->title }}</td>
                    <td><span class="badge bg-info">{{ ucfirst($document->type) }}</span></td>
                    <td>{{ $document->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('employees.documents.download', [$employee, $document]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                        <form action="{{ route('employees.documents.destroy', [$employee, $document]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No documents uploaded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Leave Balance Tab -->
    <div class="tab-pane fade" id="leave" role="tabpanel">
        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>Leave Type</th>
                    <th>Entitled Days</th>
                    <th>Taken</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveBalances ?? [] as $balance)
                <tr>
                    <td>{{ $balance->leaveType->name ?? $balance->leave_type }}</td>
                    <td>{{ $balance->entitled_days }}</td>
                    <td>{{ $balance->taken_days }}</td>
                    <td>
                        <span class="fw-bold {{ ($balance->entitled_days - $balance->taken_days) > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $balance->entitled_days - $balance->taken_days }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No leave balances configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Salary Structure Tab -->
    <div class="tab-pane fade" id="salary" role="tabpanel">
        <form action="{{ route('employees.salary-components.store', $employee) }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Component</label>
                    <select name="component" class="form-select" required>
                        <option value="BASIC">BASIC</option>
                        <option value="HRA">HRA</option>
                        <option value="TRANSPORT">Transport Allowance</option>
                        <option value="FOOD">Food Allowance</option>
                        <option value="PHONE">Phone Allowance</option>
                        <option value="OTHER_EARNING">Other Earning</option>
                        <option value="GOSI">GOSI Deduction</option>
                        <option value="LOAN">Loan Deduction</option>
                        <option value="OTHER_DEDUCTION">Other Deduction</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="earning">Earning</option>
                        <option value="deduction">Deduction</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Add Component</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>Component</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $totalEarnings = 0; $totalDeductions = 0; @endphp
                @forelse($salaryComponents ?? [] as $component)
                @php
                    if($component->type == 'earning') $totalEarnings += $component->amount;
                    else $totalDeductions += $component->amount;
                @endphp
                <tr>
                    <td>{{ $component->component }}</td>
                    <td><span class="badge bg-{{ $component->type == 'earning' ? 'success' : 'danger' }}">{{ ucfirst($component->type) }}</span></td>
                    <td class="text-end">{{ number_format($component->amount, 2) }}</td>
                    <td>
                        <form action="{{ route('employees.salary-components.destroy', [$employee, $component]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this component?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No salary components configured.</td></tr>
                @endforelse
            </tbody>
            @if(count($salaryComponents ?? []) > 0)
            <tfoot class="table-light">
                <tr><td colspan="2" class="fw-bold">Total Earnings</td><td class="text-end fw-bold text-success">{{ number_format($totalEarnings, 2) }}</td><td></td></tr>
                <tr><td colspan="2" class="fw-bold">Total Deductions</td><td class="text-end fw-bold text-danger">{{ number_format($totalDeductions, 2) }}</td><td></td></tr>
                <tr><td colspan="2" class="fw-bold">Net Salary</td><td class="text-end fw-bold">{{ number_format($totalEarnings - $totalDeductions, 2) }}</td><td></td></tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Attendance Summary Tab -->
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>{{ $attendanceSummary['present'] ?? 0 }}</h3>
                        <p class="mb-0">Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3>{{ $attendanceSummary['absent'] ?? 0 }}</h3>
                        <p class="mb-0">Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3>{{ $attendanceSummary['half_day'] ?? 0 }}</h3>
                        <p class="mb-0">Half Day</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>{{ $attendanceSummary['on_leave'] ?? 0 }}</h3>
                        <p class="mb-0">On Leave</p>
                    </div>
                </div>
            </div>
        </div>

        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Overtime (hrs)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendance ?? [] as $record)
                <tr>
                    <td>{{ $record->date->format('d M Y') }}</td>
                    <td>
                        @php $attColors = ['present' => 'success', 'absent' => 'danger', 'half_day' => 'warning', 'leave' => 'info']; @endphp
                        <span class="badge bg-{{ $attColors[$record->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span>
                    </td>
                    <td>{{ $record->check_in ?? '-' }}</td>
                    <td>{{ $record->check_out ?? '-' }}</td>
                    <td>{{ $record->overtime_hours ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No attendance records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
