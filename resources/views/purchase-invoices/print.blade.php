<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info h1 { font-size: 22px; color: #2c3e50; margin-bottom: 5px; }
        .company-info p { margin: 1px 0; color: #555; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 24px; color: #2c3e50; margin-bottom: 5px; }
        .details { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .details .col { width: 48%; }
        .details h4 { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 5px; }
        .details p { margin: 2px 0; }
        .details-table { margin-left: auto; }
        .details-table td { padding: 3px 10px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #2c3e50; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.items tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-bottom: 25px; }
        .totals table { width: 100%; }
        .totals td { padding: 5px 10px; }
        .totals .grand-total { font-size: 14px; font-weight: bold; border-top: 2px solid #2c3e50; background: #ecf0f1; }
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
        </div>
        <div class="invoice-title">
            <h2>PURCHASE INVOICE</h2>
            <p><strong>#{{ $invoice->invoice_number }}</strong></p>
            @if($invoice->supplier_invoice_number)
            <p>Supplier Ref: {{ $invoice->supplier_invoice_number }}</p>
            @endif
        </div>
    </div>

    <div class="details">
        <div class="col">
            <h4>Supplier</h4>
            <p><strong>{{ $invoice->supplier->name ?? '' }}</strong></p>
            <p>{{ $invoice->supplier->address ?? '' }}</p>
            <p>{{ $invoice->supplier->city ?? '' }}{{ isset($invoice->supplier->country) ? ', ' . $invoice->supplier->country : '' }}</p>
            <p>{{ $invoice->supplier->email ?? '' }}</p>
        </div>
        <div class="col" style="text-align: right;">
            <table class="details-table">
                <tr><td style="color:#888">Invoice Date:</td><td><strong>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</strong></td></tr>
                <tr><td style="color:#888">Due Date:</td><td><strong>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</strong></td></tr>
                <tr><td style="color:#888">Currency:</td><td>{{ $invoice->currency }}</td></tr>
                @if($invoice->purchaseOrder)
                <tr><td style="color:#888">PO Ref:</td><td>#{{ $invoice->purchaseOrder->order_number }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr><th>#</th><th>Item</th><th>Description</th><th class="text-right">Qty</th><th class="text-right">Rate</th><th class="text-right">Discount</th><th class="text-right">Tax</th><th class="text-right">Amount</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td><td>{{ $item->item->name ?? '-' }}</td><td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td><td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td><td class="text-right">{{ number_format($item->tax_amount, 2) }}</td>
                <td class="text-right">{{ number_format($item->net_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr><td>Subtotal:</td><td class="text-right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
            <tr><td>Tax:</td><td class="text-right">{{ number_format($invoice->total_tax, 2) }}</td></tr>
            <tr><td>Discount:</td><td class="text-right">{{ number_format($invoice->total_discount, 2) }}</td></tr>
            <tr class="grand-total"><td>Total:</td><td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 2) }}</td></tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ $company->name ?? config('app.name', 'OfficeIT') }} | {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
    </div>
</body>
</html>
