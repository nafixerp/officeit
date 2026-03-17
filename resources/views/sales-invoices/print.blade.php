<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .company-info p { margin: 1px 0; color: #555; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 28px; color: #2c3e50; margin-bottom: 5px; }
        .invoice-title .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; color: #fff; }
        .badge-paid { background: #27ae60; }
        .badge-unpaid { background: #e74c3c; }
        .badge-partial { background: #f39c12; }
        .badge-draft { background: #95a5a6; }
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .details .col { width: 48%; }
        .details h4 { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 5px; }
        .details p { margin: 2px 0; }
        .details-table { margin-left: auto; }
        .details-table td { padding: 3px 10px; }
        .details-table td:first-child { color: #888; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #2c3e50; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-bottom: 25px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 10px; }
        .totals .grand-total { font-size: 14px; font-weight: bold; border-top: 2px solid #2c3e50; background: #ecf0f1; }
        .bank-details { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .bank-details h4 { font-size: 12px; margin-bottom: 8px; color: #2c3e50; }
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
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <p><strong>#{{ $invoice->invoice_number }}</strong></p>
            @php
                $bClass = match($invoice->status) {
                    'Paid' => 'badge-paid', 'Partially Paid' => 'badge-partial',
                    'Draft' => 'badge-draft', default => 'badge-unpaid',
                };
            @endphp
            <span class="badge {{ $bClass }}">{{ strtoupper($invoice->status) }}</span>
        </div>
    </div>

    {{-- Customer & Invoice Details --}}
    <div class="details">
        <div class="col">
            <h4>Bill To</h4>
            <p><strong>{{ $invoice->customer->name ?? '' }}</strong></p>
            <p>{{ $invoice->customer->address ?? '' }}</p>
            <p>{{ $invoice->customer->city ?? '' }}{{ isset($invoice->customer->country) ? ', ' . $invoice->customer->country : '' }}</p>
            @if(isset($invoice->customer->tax_number))
            <p>Tax No: {{ $invoice->customer->tax_number }}</p>
            @endif
            <p>{{ $invoice->customer->email ?? '' }}</p>
        </div>
        <div class="col" style="text-align: right;">
            <table class="details-table">
                <tr><td>Invoice Date:</td><td><strong>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</strong></td></tr>
                <tr><td>Due Date:</td><td><strong>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</strong></td></tr>
                <tr><td>Type:</td><td>{{ $invoice->invoice_type }}</td></tr>
                <tr><td>Currency:</td><td>{{ $invoice->currency }}</td></tr>
                @if($invoice->salesOrder)
                <tr><td>Sales Order:</td><td>#{{ $invoice->salesOrder->order_number }}</td></tr>
                @endif
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
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
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
            <tr><td>Subtotal:</td><td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
            <tr><td>Tax:</td><td class="text-right">{{ number_format($invoice->total_tax, 2) }}</td></tr>
            <tr><td>Discount:</td><td class="text-right">{{ number_format($invoice->total_discount, 2) }}</td></tr>
            <tr class="grand-total"><td>Total:</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td></tr>
            @if($invoice->paid_amount > 0)
            <tr><td>Paid:</td><td class="text-right">{{ number_format($invoice->paid_amount, 2) }}</td></tr>
            <tr style="font-weight:bold; color:#e74c3c;"><td>Balance Due:</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td></tr>
            @endif
        </table>
    </div>

    {{-- Bank Details --}}
    @if(isset($bankDetails))
    <div class="bank-details">
        <h4>Bank Details for Payment</h4>
        <p><strong>Bank Name:</strong> {{ $bankDetails->bank_name ?? '' }}</p>
        <p><strong>Account Name:</strong> {{ $bankDetails->account_name ?? '' }}</p>
        <p><strong>Account Number:</strong> {{ $bankDetails->account_number ?? '' }}</p>
        <p><strong>IBAN:</strong> {{ $bankDetails->iban ?? '' }}</p>
        <p><strong>Swift Code:</strong> {{ $bankDetails->swift_code ?? '' }}</p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{ $company->name ?? config('app.name', 'OfficeIT') }} | {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
    </div>
</body>
</html>
