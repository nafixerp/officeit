@extends('layouts.app')

@section('title', 'Company Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Company: {{ $company->name }}</h1>
    <div>
        <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Company Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Name:</strong><br>{{ $company->name }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Short Name:</strong><br>{{ $company->short_name ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Email:</strong><br>{{ $company->email ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Phone:</strong><br>{{ $company->phone ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Mobile:</strong><br>{{ $company->mobile ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Website:</strong><br>{{ $company->website ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Registration Number:</strong><br>{{ $company->registration_number ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Tax Registration Number:</strong><br>{{ $company->tax_registration_number ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Country:</strong><br>{{ $company->country->name ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Currency:</strong><br>{{ $company->currency->code ?? '-' }} {{ $company->currency->name ?? '' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Financial Year Start:</strong><br>{{ $company->financial_year_start?->format('Y-m-d') ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Timezone:</strong><br>{{ $company->timezone ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Invoice Prefix:</strong><br>{{ $company->invoice_prefix ?? '-' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Status:</strong><br>
                        <span class="badge bg-{{ $company->is_active ? 'success' : 'secondary' }}">
                            {{ $company->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Address:</strong><br>{{ $company->address ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @if($company->logo)
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Logo</h5></div>
            <div class="card-body text-center">
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="img-fluid" style="max-height: 200px;">
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
