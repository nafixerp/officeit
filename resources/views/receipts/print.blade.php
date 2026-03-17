<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .company-info p { margin: 1px 0; color: #555; }
        .receipt-title { text-align: right; }
        .receipt-title h2 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; }
        .receipt-title .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #fff; }
        .badge-confirmed { background: #27ae60; }
        .badge-draft { background: #95a5a6; }
        .badge-cancelled { background: #e74c3c; }
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .details .col { width: 48%; }
        .details h4 { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 5px; }
        .details p { margin: 2px 0; }
        .voucher-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .voucher-table th, .voucher-table td { padding: 10px 12px; border: 1px solid #ddd; }
        .voucher-table th { background: #2c3e50; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-words { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 30px; }
        .amount-words h4 { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 5px; }
        .amount-words p { font-size: 14px; font-weight: bold; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; padding-top: 20px; }
        .signature-line { text-align: center; width: 200px; }
        .signature-line .line { border-top: 1px solid #333; margin-bottom: 5px; }
        .signature-line p { font-size: 10px; color: #666; text-transform: uppercase; }
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; color: #888; font-size: 10px; }
        @media print { body { margin: 0; padding: 15px; } }
    </style>
</head>
<body>
    {{-- Company Header --}}
    <div class="header">
        <div class="company-info">
            <h1>{{ $company->name ?? config('app.name', 'OfficeIT') }}</h1>
            <p>{{ $company->address ?? '' }}</p>
            <p>{{ $company->city ?? '' }}{{ isset($company->country) ? ', ' . $company->country : '' }}</p>
            <p>Phone: {{ $company->phone ?? '' }} | Email: {{ $company->email ?? '' }}</p>
            @if(isset($company->tax_number))
            <p>Tax Reg. No: {{ $company->tax_number }}</p>
            @endif
        </div>
        <div class="receipt-title">
            <h2>RECEIPT VOUCHER</h2>
            <p><strong>#{{ $receipt->receipt_number }}</strong></p>
            @php
                $bClass = match($receipt->status) {
                    'Confirmed' => 'badge-confirmed', 'Draft' => 'badge-draft', default => 'badge-cancelled',
                };
            @endphp
            <span class="badge {{ $bClass }}">{{ strtoupper($receipt->status) }}</span>
        </div>
    </div>

    {{-- Receipt Details --}}
    <div class="details">
        <div class="col">
            <h4>Received From</h4>
            <p><strong>{{ $receipt->customer->name ?? '' }}</strong></p>
            <p>{{ $receipt->customer->address ?? '' }}</p>
            <p>{{ $receipt->customer->city ?? '' }}{{ isset($receipt->customer->country) ? ', ' . $receipt->customer->country : '' }}</p>
            <p>{{ $receipt->customer->email ?? '' }}</p>
        </div>
        <div class="col" style="text-align: right;">
            <table style="margin-left: auto;">
                <tr><td style="padding: 3px 10px; color: #888;">Date:</td><td style="padding: 3px 10px;"><strong>{{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</strong></td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Mode:</td><td style="padding: 3px 10px;">{{ $receipt->receipt_mode }}</td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Reference:</td><td style="padding: 3px 10px;">{{ $receipt->reference_number ?? '-' }}</td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Currency:</td><td style="padding: 3px 10px;">{{ $receipt->currency ?? 'USD' }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Voucher Table --}}
    <table class="voucher-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right; width: 150px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $receipt->narration ?? 'Payment received from ' . ($receipt->customer->name ?? 'customer') }}</td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;">{{ $receipt->currency ?? 'USD' }} {{ number_format($receipt->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Amount in Words --}}
    <div class="amount-words">
        <h4>Amount in Words</h4>
        <p>{{ $amountInWords ?? '_______________________________________________' }}</p>
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="signature-line">
            <div class="line"></div>
            <p>Received By</p>
        </div>
        <div class="signature-line">
            <div class="line"></div>
            <p>Authorized Signatory</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $company->name ?? config('app.name', 'OfficeIT') }} | {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
    </div>
</body>
</html>
