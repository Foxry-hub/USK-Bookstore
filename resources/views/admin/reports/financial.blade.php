@extends('layouts.app')

@section('content')
    <section class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Laporan Keuangan</h1>
                <p class="text-sm text-slate-600">Ringkasan pemasukan berdasarkan order yang sudah dianggap dibayar.</p>
            </div>
            <a
                href="{{ route('admin.reports.financial.download', ['from' => $fromDate, 'to' => $toDate, 'payment_method' => $paymentMethod]) }}"
                class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white sm:w-auto"
            >
                Download PDF
            </a>
        </div>

        <form method="GET" action="{{ route('admin.reports.financial') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-4">
            <div>
                <label for="from" class="mb-1 block text-xs font-semibold text-slate-700">Dari Tanggal</label>
                <input type="date" id="from" name="from" value="{{ $fromDate }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="to" class="mb-1 block text-xs font-semibold text-slate-700">Sampai Tanggal</label>
                <input type="date" id="to" name="to" value="{{ $toDate }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="payment_method" class="mb-1 block text-xs font-semibold text-slate-700">Metode Pembayaran</label>
                <select id="payment_method" name="payment_method" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="all" @selected($paymentMethod === 'all')>Semua Metode</option>
                    <option value="COD" @selected($paymentMethod === 'COD')>COD</option>
                    <option value="MIDTRANS" @selected($paymentMethod === 'MIDTRANS')>Midtrans</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full rounded-lg bg-brand-500 px-3 py-2 text-sm font-semibold text-white">Terapkan</button>
                <a href="{{ route('admin.reports.financial') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold text-slate-700">Reset</a>
            </div>
        </form>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Pemasukan</p>
                <p class="mt-2 text-xl font-extrabold text-slate-900">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Order Dibayar</p>
                <p class="mt-2 text-xl font-extrabold text-slate-900">{{ $summary['total_paid_orders'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rata-Rata / Order</p>
                <p class="mt-2 text-xl font-extrabold text-slate-900">Rp {{ number_format($summary['average_order_value'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pemasukan COD</p>
                <p class="mt-2 text-xl font-extrabold text-slate-900">Rp {{ number_format($summary['cod_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pemasukan Midtrans</p>
                <p class="mt-2 text-xl font-extrabold text-slate-900">Rp {{ number_format($summary['midtrans_revenue'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $order->order_code }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->payment_method }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $order->status }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">Tidak ada data transaksi pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $orders->links() }}</div>
    </section>
@endsection
