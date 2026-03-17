@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Settings</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button" role="tab">Invoice</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">Email</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">Backup</button>
            </li>
        </ul>

        <div class="tab-content pt-4" id="settingsTabContent">
            {{-- General Tab --}}
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <form action="{{ route('settings.update', 'general') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_logo" class="form-label">Company Logo</label>
                            <input type="file" class="form-control @error('company_logo') is-invalid @enderror" id="company_logo" name="company_logo" accept="image/*">
                            @error('company_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if(!empty($settings['company_logo']))
                                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" class="mt-2" style="max-height: 60px;">
                            @endif
                        </div>
                        <div class="col-12 mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address" name="company_address" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                            @error('company_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="company_email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </form>
            </div>

            {{-- Invoice Tab --}}
            <div class="tab-pane fade" id="invoice" role="tabpanel">
                <form action="{{ route('settings.update', 'invoice') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV-') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="invoice_numbering_format" class="form-label">Numbering Format</label>
                            <select class="form-select" id="invoice_numbering_format" name="invoice_numbering_format">
                                @foreach(['SEQUENTIAL' => 'Sequential (001, 002, 003)', 'YEARLY' => 'Yearly (2026-001)', 'MONTHLY' => 'Monthly (2026-03-001)'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('invoice_numbering_format', $settings['invoice_numbering_format'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quotation_prefix" class="form-label">Quotation Prefix</label>
                            <input type="text" class="form-control" id="quotation_prefix" name="quotation_prefix" value="{{ old('quotation_prefix', $settings['quotation_prefix'] ?? 'QTN-') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="purchase_order_prefix" class="form-label">Purchase Order Prefix</label>
                            <input type="text" class="form-control" id="purchase_order_prefix" name="purchase_order_prefix" value="{{ old('purchase_order_prefix', $settings['purchase_order_prefix'] ?? 'PO-') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Invoice Settings</button>
                </form>
            </div>

            {{-- Email Tab --}}
            <div class="tab-pane fade" id="email" role="tabpanel">
                <form action="{{ route('settings.update', 'email') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="smtp_encryption" class="form-label">Encryption</label>
                            <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mail_from_address" class="form-label">From Address</label>
                            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                </form>
            </div>

            {{-- Backup Tab --}}
            <div class="tab-pane fade" id="backup" role="tabpanel">
                <div class="row">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Database Backup</h5>
                            <p class="mb-0">Use the button below to create a backup of your database. Backups are stored in the <code>storage/app/backups</code> directory.</p>
                        </div>
                        <form action="{{ url('/settings/backup') }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Create Backup Now</button>
                        </form>

                        @if(!empty($backups))
                            <h5 class="mt-4">Previous Backups</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($backups as $backup)
                                            <tr>
                                                <td>{{ $backup['name'] ?? '-' }}</td>
                                                <td>{{ $backup['size'] ?? '-' }}</td>
                                                <td>{{ $backup['date'] ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ url('/settings/backup/download/' . ($backup['name'] ?? '')) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
