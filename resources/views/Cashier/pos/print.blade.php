<!DOCTYPE html>
<html>
<head>
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 58mm; margin: 0; padding: 5px; background: #fff; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed black; margin: 5px 0; }
        table { width: 100%; }
        .bold { font-weight: bold; }
        .small { font-size: 10px; }
    </style>
</head>
<body onload="window.print(); setTimeout(window.close, 1000);">
    
    <div class="text-center">
        <div class="bold" style="font-size: 14px;">FADLI FAJAR</div>
        <div class="small">Jalan Poros Makmur No. 123</div>
        <div class="small">Telp: 0812-3456-7890</div>
    </div>

    <div class="line"></div>

    <table class="small">
        <tr><td>No</td><td>: {{ $transaction->invoice_number }}</td></tr>
        <tr><td>Tgl</td><td>: {{ $transaction->created_at->format('d/m/y H:i') }}</td></tr>
        <tr><td>Kasir</td><td>: {{ $transaction->user->name }}</td></tr>
        <tr><td>Plg</td><td>: {{ $transaction->customer->name ?? 'Umum' }}</td></tr>
    </table>

    <div class="line"></div>

    <table>
        @foreach($transaction->details as $item)
        <tr>
            <td colspan="2" class="bold">{{ $item->productUnit->product->name }}</td>
        </tr>
        <tr>
            <td>{{ $item->quantity }} {{ $item->productUnit->unit->short_name }} x {{ number_format($item->price_at_purchase, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td>Total Item</td>
            <td class="text-right">{{ $transaction->total_items }}</td>
        </tr>
        <tr>
            <td class="bold" style="font-size: 14px;">TOTAL</td>
            <td class="text-right bold" style="font-size: 14px;">{{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>TUNAI</td>
            <td class="text-right">{{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>KEMBALI</td>
            <td class="text-right">{{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>
    
    <div class="text-center small" style="margin-top: 10px;">
        Terima Kasih<br>
        Barang yang dibeli tidak dapat ditukar
    </div>

</body>
</html>