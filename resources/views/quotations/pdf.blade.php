<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation #{{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .company-info h1 { margin: 0; font-size: 22px; color: #1a1a1a; }
        .company-info p { margin: 2px 0; }
        .document-title { text-align: right; }
        .document-title h2 { margin: 0; font-size: 28px; color: #555; text-transform: uppercase; }
        .details-section { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .details-section .col { width: 48%; }
        .details-section h4 { margin: 0 0 5px; font-size: 11px; text-transform: uppercase; color: #888; }
        .details-section p { margin: 2px 0; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f5f5f5; border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.items td { border: 1px solid #ddd; padding: 8px; }
        table.items td.text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 8px; }
        .totals .grand-total { font-size: 14px; font-weight: bold; border-top: 2px solid #333; }
        .terms { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; }
        .terms h4 { margin: 0 0 5px; font-size: 12px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; color: #fff; }
        .status-Draft { background: #6c757d; }
        .status-Sent { background: #0dcaf0; }
        .status-Approved { background: #198754; }
        .status-Rejected { background: #dc3545; }
        .status-Converted { background: #0d6efd; }
        .status-Expired { background: #ffc107; color: #333; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    {{-- Company Header --}}
    <div class="header">
        <div class="company-info">
            <h1>{{ $company->name ?? config('app.name', 'OfficeIT') }}</h1>
            <p>{{ $company->address ?? '' }}</p>
            <p>{{ $company->city ?? '' }} {{ $company->country ?? '' }}</p>
            <p>Phone: {{ $company->phone ?? '' }} | Email: {{ $company->email ?? '' }}</p>
            @if(isset($company->tax_number))
            <p>Tax Number: {{ $company->tax_number }}</p>
            @endif
        </div>
        <div class="document-title">
            <h2>Quotation</h2>
            <p><strong>#{{ $quotation->quotation_number }}</strong></p>
            <p><span class="status-badge status-{{ $quotation->status }}">{{ $quotation->status }}</span></p>
        </div>
    </div>

    {{-- Quotation Details --}}
    <div class="details-section">
        <div class="col">
            <h4>Quotation To</h4>
            <p><strong>{{ $quotation->customer->name ?? '' }}</strong></p>
            <p>{{ $quotation->customer->address ?? '' }}</p>
            <p>{{ $quotation->customer->city ?? '' }} {{ $quotation->customer->country ?? '' }}</p>
            <p>{{ $quotation->customer->email ?? '' }}</p>
        </div>
        <div class="col" style="text-align: right;">
            <table style="margin-left: auto;">
                <tr><td style="padding: 2px 10px; color: #888;">Date:</td><td style="padding: 2px 0;">{{ \Carbon\Carbon::parse($quotation->date)->format('d M Y') }}</td></tr>
                <tr><td style="padding: 2px 10px; color: #888;">Valid Until:</td><td style="padding: 2px 0;">{{ $quotation->validity_date ? \Carbon\Carbon::parse($quotation->validity_date)->format('d M Y') : '-' }}</td></tr>
                <tr><td style="padding: 2px 10px; color: #888;">Currency:</td><td style="padding: 2px 0;">{{ $quotation->currency }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Items Table --}}
    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>Item / Service</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $item->item->name ?? '-' }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right">{{ number_format($item->net_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>Tax:</td>
                <td class="text-right">{{ number_format($quotation->total_tax, 2) }}</td>
            </tr>
            <tr>
                <td>Discount:</td>
                <td class="text-right">{{ number_format($quotation->total_discount, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td>Total:</td>
                <td class="text-right">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Terms --}}
    <div class="terms">
        <h4>Terms &amp; Conditions</h4>
        <p>{{ $quotation->remarks ?? 'Standard terms and conditions apply.' }}</p>
    </div>
</body>
</html>
