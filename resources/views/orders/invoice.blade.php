<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        @page {
            margin: 18mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 11px;
            line-height: 1.5;
        }

        .invoice-wrap {
            width: 100%;
            max-width: 100%;
        }

        .header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand {
            width: 58%;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            display: inline-block;
            margin-right: 8px;
            padding: 8px;
            vertical-align: top;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            display: inline-block;
            vertical-align: top;
            margin-top: 3px;
        }

        .store-name {
            font-size: 16px;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .store-meta {
            margin: 2px 0 0;
            color: #475569;
            font-size: 10px;
        }

        .invoice-meta {
            width: 42%;
            text-align: right;
        }

        .invoice-title {
            margin: 0;
            font-size: 18px;
            letter-spacing: 1.2px;
        }

        .meta-line {
            margin-top: 2px;
            color: #334155;
        }

        .meta-label {
            color: #64748b;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
        }

        .buyer-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .buyer-name {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .buyer-line {
            margin: 2px 0 0;
            color: #475569;
            font-size: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .items-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 10px;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .items-table tbody td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 4px 0;
            font-size: 10px;
        }

        .summary-label {
            color: #475569;
            width: 70%;
            text-align: right;
            padding-right: 10px;
        }

        .summary-value {
            width: 30%;
            text-align: right;
            color: #0f172a;
        }

        .grand-total td {
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .thank-you {
            margin-top: 18px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            text-align: center;
        }

        .thank-you h3 {
            margin: 0;
            font-size: 12px;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .thank-you p {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 10px;
        }

        @media screen and (max-width: 540px) {
            .header-table,
            .header-table tbody,
            .header-table tr,
            .header-table td {
                display: block;
                width: 100%;
                text-align: left;
            }

            .invoice-meta {
                margin-top: 10px;
                text-align: left;
            }

            .summary-label,
            .summary-value {
                width: 50%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-wrap">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="brand">
                        <span class="brand-mark">
                            <img src="{{ public_path('assets/logo.png') }}" alt="Logo BookStore">
                        </span>
                        <div class="brand-name">
                            <p class="store-name">BookStore</p>
                            <p class="store-meta">Jl. Buku Nusantara No. 12, Indonesia</p>
                            <p class="store-meta">Email: hello@uskbookstore.com | Telp: +62 812-0000-0000</p>
                        </div>
                    </td>
                    <td class="invoice-meta">
                        <p class="invoice-title">INVOICE</p>
                        <p class="meta-line"><span class="meta-label">No:</span> {{ $invoiceNumber }}</p>
                        <p class="meta-line"><span class="meta-label">Tanggal:</span> {{ $invoiceDate->format('d M Y') }}</p>
                        <p class="meta-line"><span class="meta-label">Order Ref:</span> {{ $order->order_code }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body">
            <h2 class="section-title">Informasi Pembeli</h2>
            <div class="buyer-box">
                <p class="buyer-name">{{ $order->user->name }}</p>
                <p class="buyer-line">No. Telepon: {{ $order->phone }}</p>
                <p class="buyer-line">Alamat Pengiriman: {{ $order->shipping_address }}</p>
            </div>

            <h2 class="section-title">Daftar Pesanan</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                                <table class="summary">
                                    <tr class="grand-total">
                                        <td class="summary-label">Total Harga</td>
                                        <td class="summary-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @if ($order->payment_method === 'CASH' && $order->cash_payment_confirmed_at)
                                        <tr>
                                            <td class="summary-label">Uang Diterima</td>
                                            <td class="summary-value">Rp {{ number_format($order->cash_amount_paid, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="summary-label">Kembalian</td>
                                            <td class="summary-value">Rp {{ number_format($order->cash_change, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                </table>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="summary">
                <tr class="grand-total">
                    <td class="summary-label">Total Harga</td>
                    <td class="summary-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="thank-you">
            <h3>Thank You Note</h3>
            <p>Terima kasih telah berbelanja di BookStore.</p>
            <p>Kami berharap buku pilihan Anda membawa inspirasi baru setiap hari.</p>
        </div>
    </div>
</body>
</html>
