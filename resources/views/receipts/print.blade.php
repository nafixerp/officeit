<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .voucher { border: 2px solid #333; padding: 25px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin-bottom: 3px; }
        .header h2 { font-size: 16px; color: #555; text-transform: uppercase; letter-spacing: 3px; }
        .details { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .details .col { width: 48%; }
        .details p { margin: 3px 0; }
        .amount-box { text-align: center; background: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .amount-box .label { font-size: 11px; text-transform: uppercase; color: #888; }
        table.alloc { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.alloc th { background: #f5f5f5; border: 1px solid #ddd; padding: 6px 10px; font-size: 11px; text-transform: uppercase; }
        table.alloc td { border: 1px solid #ddd; padding: 6px 10px; }
        .text-right { text-align: right; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding-top: 20px; }
        .signatures .sig { text-align: center; width: 30%; }
        .signatures .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 11px; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #888; }
        @media print { body { margin: 0; padding: 10px; } .voucher { border: 1px solid #000; } }
    </style>
</head>
<body>
    <div class="voucher">
        <div class="header">
            <h1>{{ $company->name ?? config('app.name', 'OfficeIT') }}</h1>
            <p>{{ $company->address ?? '' }} {{ $company->city ?? '' }}</p>
            <h2>Receipt Voucher</h2>
        </div>

        <div class="details">
            <div class="col">
                <p><strong>Receipt #:</strong> {{ $receipt->receipt_number }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($receipt->date)->format('d M Y') }}</p>
                <p><strong>Mode:</strong> {{ $receipt->receipt_mode }}</p>
                @if($receipt->reference_number)
                <p><strong>Reference:</strong> {{ $receipt->reference_number }}</p>
                @endif
            </div>
            <div class="col" style="text-align: right;">
                <p><strong>Received From:</strong></p>
                <p>{{ $receipt->customer->name ?? '' }}</p>
                <p>{{ $receipt->customer->address ?? '' }}</p>
            </div>
        </div>

        <div class="amount-box">
            <div class="label">Amount Received</div>
            <div class="amount">{{ $receipt->currency }} {{ number_format($receipt->amount, 2) }}</div>
        </div>

        @if(($receipt->allocations ?? collect())->count() > 0)
        <table class="alloc">
            <thead>
                <tr><th>Invoice #</th><th class="text-right">Invoice Amount</th><th class="text-right">Allocated</th></tr>
            </thead>
            <tbody>
                @foreach($receipt->allocations as $alloc)
                <tr>
                    <td>{{ $alloc->invoice->invoice_number ?? '-' }}</td>
                    <td class="text-right">{{ number_format($alloc->invoice->total_amount ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($alloc->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if($receipt->narration)
        <p><strong>Narration:</strong> {{ $receipt->narration }}</p>
        @endif

        <div class="signatures">
            <div class="sig"><div class="sig-line">Received By</div></div>
            <div class="sig"><div class="sig-line">Approved By</div></div>
            <div class="sig"><div class="sig-line">Customer Signature</div></div>
        </div>

        <div class="footer">
            <p>{{ $company->name ?? config('app.name', 'OfficeIT') }} | {{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
        </div>
    </div>
</body>
</html>
