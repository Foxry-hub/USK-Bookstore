@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-3xl font-bold text-slate-900">Riwayat Pesanan Saya</h1>
        <p class="mt-2 text-sm text-slate-600">Semua pesanan kamu ada di sini, termasuk pembayaran online Midtrans.</p>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                @php
                    $statusClasses = [
                        'Menunggu Pembayaran' => 'bg-amber-100 text-amber-800',
                        'Menunggu Konfirmasi' => 'bg-blue-100 text-blue-800',
                        'Menunggu Verifikasi' => 'bg-indigo-100 text-indigo-800',
                        'Dibayar' => 'bg-emerald-100 text-emerald-800',
                        'Diproses' => 'bg-cyan-100 text-cyan-800',
                        'Dikirim' => 'bg-sky-100 text-sky-800',
                        'Selesai' => 'bg-green-100 text-green-800',
                        'Pembayaran Gagal' => 'bg-rose-100 text-rose-800',
                        'Refund' => 'bg-slate-200 text-slate-700',
                    ];

                    $paymentStatusClasses = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'settlement' => 'bg-emerald-100 text-emerald-800',
                        'capture' => 'bg-emerald-100 text-emerald-800',
                        'deny' => 'bg-rose-100 text-rose-800',
                        'cancel' => 'bg-rose-100 text-rose-800',
                        'expire' => 'bg-rose-100 text-rose-800',
                        'refund' => 'bg-slate-200 text-slate-700',
                        'partial_refund' => 'bg-slate-200 text-slate-700',
                    ];
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->status }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Total: <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></p>
                    <p class="text-sm text-slate-600">Metode Bayar: {{ $order->payment_method }}</p>
                    @if ($order->midtrans_transaction_status)
                        <div class="mt-1 text-sm text-slate-600">
                            <span>Status Payment Gateway:</span>
                            <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $paymentStatusClasses[$order->midtrans_transaction_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->midtrans_transaction_status }}</span>
                        </div>
                    @endif

                    @if ($order->payment_method !== 'COD' && in_array($order->status, ['Menunggu Pembayaran', 'Pembayaran Gagal'], true))
                        <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Bayar Ulang</button>
                        </form>
                    @endif

                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between text-sm text-slate-700">
                                <span>{{ $item->book->title }} x {{ $item->quantity }}</span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada pesanan yang dibuat.
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </section>
@endsection
