@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Settings</h1>
</div>

<ul class="nav nav-tabs" id="settingsTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="invoice-tab" data-bs-toggle="tab" href="#invoice" role="tab">Invoice</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="email-tab" data-bs-toggle="tab" href="#email" role="tab">Email</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="backup-tab" data-bs-toggle="tab" href="#backup" role="tab">Backup</a>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom" id="settingsTabContent">
    <!-- General Settings -->
    <div class="tab-pane fade show active p-4" id="general" role="tabpanel">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="general">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="company_email" class="form-label">Company Email</label>
                    <input type="email" name="company_email" id="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="company_phone" class="form-label">Company Phone</label>
                    <input type="text" name="company_phone" id="company_phone" class="form-control" value="{{ $settings['company_phone'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="company_website" class="form-label">Website</label>
                    <input type="url" name="company_website" id="company_website" class="form-control" value="{{ $settings['company_website'] ?? '' }}">
                </div>
                <div class="col-12">
                    <label for="company_address" class="form-label">Address</label>
                    <textarea name="company_address" id="company_address" class="form-control" rows="2">{{ $settings['company_address'] ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="company_logo" class="form-label">Company Logo</label>
                    @if(isset($settings['company_logo']))
                        <div class="mb-2"><img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo" style="max-height:60px;"></div>
                    @endif
                    <input type="file" name="company_logo" id="company_logo" class="form-control" accept="image/*">
                </div>
                <div class="col-md-3">
                    <label for="default_currency" class="form-label">Default Currency</label>
                    <input type="text" name="default_currency" id="default_currency" class="form-control" value="{{ $settings['default_currency'] ?? 'SAR' }}">
                </div>
                <div class="col-md-3">
                    <label for="fiscal_year_start" class="form-label">Fiscal Year Start</label>
                    <select name="fiscal_year_start" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ ($settings['fiscal_year_start'] ?? 1) == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tax_number" class="form-label">Tax Registration Number (VAT)</label>
                    <input type="text" name="tax_number" id="tax_number" class="form-control" value="{{ $settings['tax_number'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="cr_number" class="form-label">Commercial Registration Number</label>
                    <input type="text" name="cr_number" id="cr_number" class="form-control" value="{{ $settings['cr_number'] ?? '' }}">
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save General Settings</button>
            </div>
        </form>
    </div>

    <!-- Invoice Settings -->
    <div class="tab-pane fade p-4" id="invoice" role="tabpanel">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="invoice">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" id="invoice_prefix" class="form-control" value="{{ $settings['invoice_prefix'] ?? 'INV-' }}">
                </div>
                <div class="col-md-6">
                    <label for="invoice_next_number" class="form-label">Next Invoice Number</label>
                    <input type="number" name="invoice_next_number" id="invoice_next_number" class="form-control" value="{{ $settings['invoice_next_number'] ?? 1 }}">
                </div>
                <div class="col-md-6">
                    <label for="quotation_prefix" class="form-label">Quotation Prefix</label>
                    <input type="text" name="quotation_prefix" id="quotation_prefix" class="form-control" value="{{ $settings['quotation_prefix'] ?? 'QT-' }}">
                </div>
                <div class="col-md-6">
                    <label for="payment_terms" class="form-label">Default Payment Terms (days)</label>
                    <input type="number" name="payment_terms" id="payment_terms" class="form-control" value="{{ $settings['payment_terms'] ?? 30 }}">
                </div>
                <div class="col-12">
                    <label for="invoice_notes" class="form-label">Default Invoice Notes</label>
                    <textarea name="invoice_notes" id="invoice_notes" class="form-control" rows="3">{{ $settings['invoice_notes'] ?? '' }}</textarea>
                </div>
                <div class="col-12">
                    <label for="invoice_terms" class="form-label">Default Invoice Terms & Conditions</label>
                    <textarea name="invoice_terms" id="invoice_terms" class="form-control" rows="3">{{ $settings['invoice_terms'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Invoice Settings</button>
            </div>
        </form>
    </div>

    <!-- Email Settings -->
    <div class="tab-pane fade p-4" id="email" role="tabpanel">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="email">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="mail_driver" class="form-label">Mail Driver</label>
                    <select name="mail_driver" class="form-select">
                        <option value="smtp" {{ ($settings['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="sendmail" {{ ($settings['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                        <option value="mailgun" {{ ($settings['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="mail_host" class="form-label">SMTP Host</label>
                    <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="mail_port" class="form-label">SMTP Port</label>
                    <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? 587 }}">
                </div>
                <div class="col-md-4">
                    <label for="mail_username" class="form-label">Username</label>
                    <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ $settings['mail_username'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="mail_password" class="form-label">Password</label>
                    <input type="password" name="mail_password" id="mail_password" class="form-control" value="{{ $settings['mail_password'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="mail_from_address" class="form-label">From Address</label>
                    <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label for="mail_from_name" class="form-label">From Name</label>
                    <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] ?? '' }}">
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Email Settings</button>
            </div>
        </form>
    </div>

    <!-- Backup Settings -->
    <div class="tab-pane fade p-4" id="backup" role="tabpanel">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Database Backup</h5>
                        <p class="text-muted">Download a backup of your database</p>
                        <form action="{{ url('/settings/backup/database') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Download Database Backup</button>
                        </form>
                        @if(isset($lastBackup))
                            <small class="text-muted mt-2 d-block">Last backup: {{ $lastBackup }}</small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Full Backup</h5>
                        <p class="text-muted">Download a full backup (database + files)</p>
                        <form action="{{ url('/settings/backup/full') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="bi bi-download"></i> Download Full Backup</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($backupFiles) && count($backupFiles) > 0)
        <div class="card mt-4">
            <div class="card-header"><h5 class="mb-0">Existing Backups</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr><th>File</th><th>Size</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($backupFiles as $file)
                        <tr>
                            <td>{{ $file['name'] }}</td>
                            <td>{{ $file['size'] }}</td>
                            <td>{{ $file['date'] }}</td>
                            <td>
                                <a href="{{ $file['url'] }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                <form action="{{ url('/settings/backup/' . $file['name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
