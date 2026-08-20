<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $sale->invoice_number }}</title>
    <style>
        body{font-family:dejavusans,sans-serif;color:#172033;font-size:11px;margin:0;background:#fff}
        .header{background:#102a56;color:#fff;padding:26px 30px;border-bottom:6px solid #2f80ed}
        .header table,.header tr,.header td,.header div,.header span{color:#fff}
        .brand{font-size:23px;font-weight:bold;line-height:1.25}.invoice-title{font-size:28px;font-weight:bold;text-align:right;letter-spacing:1px}
        .invoice-number{font-size:14px;color:#dbeafe;letter-spacing:0}.muted{color:#52627a}.white-muted{color:#dbe5f3;line-height:1.6}
        .details{padding:24px 30px 20px}.section-label{color:#2f80ed;font-size:9px;font-weight:bold;letter-spacing:1px;margin-bottom:5px}
        .box{background:#f5f8fc;border:1px solid #d7e0ec;padding:15px;min-height:76px}.right{text-align:right}.center{text-align:center}
        table{width:100%;border-collapse:collapse}.items{padding:0 30px}
        .item-table{border:1px solid #ccd7e5}.item-table th{background:#102a56;color:#fff;text-align:left;padding:11px 9px;font-size:10px}
        .item-table td{padding:11px 9px;border-bottom:1px solid #dce4ee;vertical-align:middle}
        .item-table tbody tr:nth-child(even) td{background:#f7f9fc}
        .totals{width:45%;margin-left:55%;margin-top:18px;border:1px solid #d3dce8}
        .totals td{padding:8px 10px;border-bottom:1px solid #e0e6ee;text-align:right;white-space:nowrap}
        .totals td:first-child{width:62%}.totals td:last-child{width:38%;font-weight:bold}
        .grand td{background:#102a56;color:#fff;font-size:14px;font-weight:bold;padding:11px 10px}
        .footer{margin:30px;padding-top:15px;border-top:1px solid #d7e0ec;color:#52627a;text-align:center;font-size:10px}
    </style>
</head>
<body>
    <div class="header">
        <table><tr>
            <td style="border:0;padding:0"><div class="brand">{{ data_get($company, 'name', config('app.name')) }}</div><div class="white-muted">{{ data_get($company, 'address') }}</div><div class="white-muted">{{ data_get($company, 'mobile') }} · {{ data_get($company, 'email') }}</div></td>
            <td style="border:0;padding:0" class="invoice-title">INVOICE<br><span class="invoice-number">{{ $sale->invoice_number }}</span></td>
        </tr></table>
    </div>
    <div class="details">
        <table><tr>
            <td style="width:58%;border:0;padding:0 12px 0 0"><div class="box"><div class="section-label">BILL TO</div><strong style="font-size:14px">{{ trim($sale->customer->first_name.' '.$sale->customer->last_name) }}</strong><br><span class="muted">{{ $sale->customer->billing_address }}<br>{{ $sale->customer->mobile }}@if($sale->customer->tax_number)<br>GSTIN: {{ $sale->customer->tax_number }}@endif</span></div></td>
            <td style="width:42%;border:0;padding:0"><div class="box"><div class="section-label">INVOICE DETAILS</div><table><tr><td style="border:0;padding:5px 0;color:#52627a">Invoice Date</td><td style="border:0;padding:5px 0" class="right"><strong>{{ $sale->sale_date->format('d M Y') }}</strong></td></tr><tr><td style="border:0;padding:5px 0;color:#52627a">Sale Reference</td><td style="border:0;padding:5px 0" class="right"><strong>{{ $sale->sale_code }}</strong></td></tr></table></div></td>
        </tr></table>
    </div>
    <div class="items">
        <table class="item-table">
            <thead><tr><th style="width:6%" class="center">#</th><th style="width:38%">Reel Product</th><th style="width:12%" class="center">Quantity</th><th style="width:14%" class="right">Price</th><th style="width:14%" class="right">Discount</th><th style="width:16%" class="right">Amount</th></tr></thead>
            <tbody>@foreach($products as $product)<tr><td class="center">{{ $loop->iteration }}</td><td><strong>{{ $product['reel']->code }}</strong></td><td class="center">{{ $product['quantity'] }}</td><td class="right">{{ number_format($product['unit_price'],2) }}</td><td class="right">{{ number_format($product['discount'],2) }}</td><td class="right"><strong>{{ number_format($product['amount'],2) }}</strong></td></tr>@endforeach</tbody>
        </table>
        <table class="totals">
            <tr><td>Subtotal</td><td class="right">{{ number_format($sale->subtotal,2) }}</td></tr>
            <tr><td>Discount</td><td class="right">{{ number_format($sale->discount,2) }}</td></tr>
            @if($sale->is_gst_applicable)
                <tr><td>SGST ({{ number_format($sale->sgst_percentage,2) }}%)</td><td class="right">{{ number_format($sale->sgst_amount,2) }}</td></tr>
                <tr><td>CGST ({{ number_format($sale->cgst_percentage,2) }}%)</td><td class="right">{{ number_format($sale->cgst_amount,2) }}</td></tr>
            @endif
            <tr class="grand"><td>Total</td><td class="right">{{ number_format($sale->total,2) }}</td></tr>
        </table>
    </div>
    <div class="footer">Thank you for your business.</div>
</body>
</html>
