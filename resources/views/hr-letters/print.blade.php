<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ucfirst(str_replace('_', ' ', $letter->letter_type)) }} - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 14pt; }
        }
        body { font-family: 'Times New Roman', serif; }
        .letterhead { border-bottom: 3px solid #333; padding-bottom: 15px; margin-bottom: 30px; }
        .letter-content { line-height: 1.8; min-height: 400px; white-space: pre-wrap; }
        .signature-area { margin-top: 80px; }
    </style>
</head>
<body>
    <div class="no-print text-center py-3 bg-light border-bottom">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
        <a href="{{ route('hr-letters.show', $letter) }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="container" style="max-width:800px;">
        <div class="p-5">
            <!-- Company Letterhead -->
            <div class="letterhead text-center">
                <h2 class="mb-1">{{ $company->name ?? config('app.name', 'OfficeIT') }}</h2>
                <p class="text-muted mb-0">{{ $company->address ?? '' }}</p>
                <p class="text-muted mb-0">Tel: {{ $company->phone ?? '' }} | Email: {{ $company->email ?? '' }}</p>
                @if(isset($company->cr_number))
                <p class="text-muted mb-0">CR: {{ $company->cr_number }} | VAT: {{ $company->tax_number ?? '' }}</p>
                @endif
            </div>

            <!-- Date -->
            <div class="text-end mb-4">
                <p>Date: {{ $letter->created_at?->format('d F Y') ?? now()->format('d F Y') }}</p>
            </div>

            <!-- Recipient Details -->
            <div class="mb-4">
                @if($letter->employee)
                <p class="mb-1"><strong>{{ $letter->employee->full_name }}</strong></p>
                <p class="mb-1">Employee ID: {{ $letter->employee->employee_id_number }}</p>
                <p class="mb-1">Department: {{ $letter->employee->department->name ?? '' }}</p>
                <p class="mb-1">Designation: {{ $letter->employee->designation->name ?? '' }}</p>
                @elseif($letter->candidate)
                <p class="mb-1"><strong>{{ $letter->candidate->name }}</strong></p>
                <p class="mb-1">{{ $letter->candidate->email }}</p>
                @endif
            </div>

            <!-- Subject -->
            <div class="mb-4">
                <p><strong>Subject: {{ ucfirst(str_replace('_', ' ', $letter->letter_type)) }}</strong></p>
            </div>

            <!-- Letter Content -->
            <div class="letter-content mb-4">
                {{ $letter->content }}
            </div>

            <!-- Signature Area -->
            <div class="signature-area">
                <div class="row">
                    <div class="col-6">
                        <div class="border-top d-inline-block" style="width:200px;"></div>
                        <p class="mb-0">Authorized Signature</p>
                        <p class="text-muted">Name: _________________</p>
                        <p class="text-muted">Designation: _________________</p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="text-muted">Company Stamp</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
