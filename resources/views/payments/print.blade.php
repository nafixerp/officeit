<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment #{{ $payment->payment_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .company-info p { margin: 1px 0; color: #555; }
        .payment-title { text-align: right; }
        .payment-title h2 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; }
        .payment-title .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #fff; }
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
        <div class="payment-title">
            <h2>PAYMENT VOUCHER</h2>
            <p><strong>#{{ $payment->payment_number }}</strong></p>
            @php
                $bClass = match($payment->status) {
                    'Confirmed' => 'badge-confirmed', 'Draft' => 'badge-draft', default => 'badge-cancelled',
                };
            @endphp
            <span class="badge {{ $bClass }}">{{ strtoupper($payment->status) }}</span>
        </div>
    </div>

    <div class="details">
        <div class="col">
            <h4>Paid To</h4>
            <p><strong>{{ $payment->supplier->name ?? '' }}</strong></p>
            <p>{{ $payment->supplier->address ?? '' }}</p>
            <p>{{ $payment->supplier->city ?? '' }}{{ isset($payment->supplier->country) ? ', ' . $payment->supplier->country : '' }}</p>
            <p>{{ $payment->supplier->email ?? '' }}</p>
        </div>
        <div class="col" style="text-align: right;">
            <table style="margin-left: auto;">
                <tr><td style="padding: 3px 10px; color: #888;">Date:</td><td style="padding: 3px 10px;"><strong>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</strong></td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Mode:</td><td style="padding: 3px 10px;">{{ $payment->payment_mode }}</td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Reference:</td><td style="padding: 3px 10px;">{{ $payment->reference_number ?? '-' }}</td></tr>
                <tr><td style="padding: 3px 10px; color: #888;">Currency:</td><td style="padding: 3px 10px;">{{ $payment->currency ?? 'USD' }}</td></tr>
            </table>
        </div>
    </div>

    <table class="voucher-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right; width: 150px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->narration ?? 'Payment made to ' . ($payment->supplier->name ?? 'supplier') }}</td>
                <td style="text-align: right; font-weight: bold; font-size: 14px;">{{ $payment->currency ?? 'USD' }} {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-words">
        <h4>Amount in Words</h4>
        <p>{{ $amountInWords ?? '_______________________________________________' }}</p>
    </div>

    <div class="signatures">
        <div class="signature-line">
            <div class="line"></div>
            <p>Prepared By</p>
        </div>
        <div class="signature-line">
            <div class="line"></div>
            <p>Approved By</p>
        </div>
        <div class="signature-line">
            <div class="line"></div>
            <p>Received By</p>
        </div>
    </div>

    <div class="footer">
        <p>{{ $company->name ?? config('app.name', 'OfficeIT') }} | {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
    </div>
</body>
</html>
