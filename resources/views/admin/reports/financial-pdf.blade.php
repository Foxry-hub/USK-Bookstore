<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; }
        h1 { margin: 0 0 8px; font-size: 20px; }
        .muted { color: #475569; }
        .header { margin-bottom: 16px; }
        .summary { width: 100%; border-collapse: collapse; margin: 12px 0 14px; }
        .summary td { border: 1px solid #cbd5e1; padding: 8px; }
        .summary .label { background: #f8fafc; width: 38%; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 7px; }
        .table th { background: #e2e8f0; text-align: left; }
        .text-right { text-align: right; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan BookStore</h1>
        <p class="muted small">Periode: {{ $fromDate }} s/d {{ $toDate }}</p>
        <p class="muted small">Metode: {{ $paymentMethod === 'all' ? 'Semua' : $paymentMethod }}</p>
        <p class="muted small">Dicetak: {{ $printedAt->format('d M Y H:i') }}</p>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Total Pemasukan</td>
            <td>Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Order Dibayar</td>
            <td>{{ $summary['total_paid_orders'] }}</td>
        </tr>
        <tr>
            <td class="label">Rata-Rata per Order</td>
            <td>Rp {{ number_format($summary['average_order_value'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Pemasukan COD</td>
            <td>Rp {{ number_format($summary['cod_revenue'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Pemasukan Midtrans</td>
            <td>Rp {{ number_format($summary['midtrans_revenue'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Order</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Metode</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_code }}</td>
                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td>{{ $order->user->name ?? '-' }}</td>
                    <td>{{ $order->payment_method }}</td>
                    <td>{{ $order->status }}</td>
                    <td class="text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
