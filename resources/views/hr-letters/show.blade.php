@extends('layouts.app')
@section('title', 'HR Letter Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">HR Letter Details</h1>
    <div>
        <a href="{{ route('hr-letters.print', $letter) }}" class="btn btn-primary" target="_blank"><i class="bi bi-printer"></i> Print</a>
        <a href="{{ route('hr-letters.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <strong>Recipient:</strong> {{ $letter->employee->full_name ?? $letter->candidate->name ?? '-' }}
            </div>
            <div class="col-md-3">
                <strong>Type:</strong> <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $letter->letter_type)) }}</span>
            </div>
            <div class="col-md-3">
                <strong>Date:</strong> {{ $letter->created_at?->format('d M Y') }}
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="border p-4" style="min-height:400px;white-space:pre-wrap;">{{ $letter->content }}</div>
    </div>
</div>
@endsection
