@extends('layouts.app')

@section('content')
    <section>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Keterangan Paket</h1>
                <p class="mt-2 text-sm text-slate-600">Di sini kamu bisa lihat semua paket, dari diproses, dikirim, dibatalkan, sampai selesai.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Lengkapi Profile</a>
        </div>

        @php
            $filterItems = [
                'all' => 'Semua',
                'menunggu_konfirmasi' => 'Menunggu',
                'dibayar' => 'Dibayar',
                'diproses' => 'Diproses',
                'dikirim' => 'Dikirim',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
            ];
        @endphp

        <div class="mt-5 flex flex-wrap gap-2">
            @foreach ($filterItems as $key => $label)
                <a href="{{ route('orders.index', ['status' => $key]) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ $activePackageStatus === $key ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50' }}">
                    {{ $label }}
                    <span class="ml-1 text-xs {{ $activePackageStatus === $key ? 'text-slate-200' : 'text-slate-500' }}">({{ $packageStats[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Semua Paket</p>
                <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $packageStats['all'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Diproses</p>
                <p class="mt-2 text-2xl font-extrabold text-cyan-700">{{ $packageStats['diproses'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Dikirim</p>
                <p class="mt-2 text-2xl font-extrabold text-sky-700">{{ $packageStats['dikirim'] ?? 0 }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Dibatalkan</p>
                <p class="mt-2 text-2xl font-extrabold text-rose-700">{{ $packageStats['dibatalkan'] ?? 0 }}</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                @php
                    $statusClasses = [
                        'Menunggu Konfirmasi' => 'bg-blue-100 text-blue-800',
                        'Menunggu Verifikasi' => 'bg-indigo-100 text-indigo-800',
                        'Dibayar' => 'bg-emerald-100 text-emerald-800',
                        'Diproses' => 'bg-cyan-100 text-cyan-800',
                        'Dikirim' => 'bg-sky-100 text-sky-800',
                        'Selesai' => 'bg-green-100 text-green-800',
                        'Pembayaran Gagal' => 'bg-rose-100 text-rose-800',
                        'Refund' => 'bg-slate-200 text-slate-700',
                    ];

                    $statusLabels = [
                        'Pembayaran Gagal' => 'Dibatalkan',
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
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Total: <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></p>
                    <p class="text-sm text-slate-600">Metode Bayar: {{ $order->payment_method }}</p>
                    <p class="text-sm text-slate-600">Alamat Kirim: {{ $order->shipping_address }}</p>
                    <p class="text-sm text-slate-600">No. Telp: {{ $order->phone }}</p>
                    @if ($order->midtrans_transaction_status)
                        <div class="mt-1 text-sm text-slate-600">
                            <span>Status Payment Gateway:</span>
                            <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $paymentStatusClasses[$order->midtrans_transaction_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->midtrans_transaction_status }}</span>
                        </div>
                    @endif

                    @if ($order->status === 'Dikirim')
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                            @if ($order->estimated_delivery_at && now()->greaterThanOrEqualTo($order->estimated_delivery_at))
                                Paket diperkirakan sudah sampai. Silakan konfirmasi kalau sudah diterima.
                            @else
                                Paket sedang dalam perjalanan. Estimasi sampai: {{ $order->estimated_delivery_at?->format('d M Y, H:i') ?? '-' }}.
                            @endif
                        </div>
                    @endif

                    @if ($order->status === 'Diproses')
                        <div class="mt-3 rounded-xl border border-cyan-200 bg-cyan-50 p-3 text-sm text-cyan-900">
                            Paket kamu sedang diproses oleh admin.
                        </div>
                    @endif

                    @if ($order->status === 'Pembayaran Gagal')
                        <div class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-900">
                            Paket atau pembayaran ini dibatalkan. Jika perlu, kamu bisa buat pesanan baru.
                        </div>
                    @endif

                    @if ($order->status === 'Dikirim' && $order->estimated_delivery_at && now()->greaterThanOrEqualTo($order->estimated_delivery_at) && ! $order->received_at)
                        <form action="{{ route('orders.confirm-received', $order) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Konfirmasi Pesanan Diterima</button>
                        </form>
                    @endif

                    @if ($order->status === 'Dikirim' && $order->estimated_delivery_at && now()->lessThan($order->estimated_delivery_at))
                        <p class="mt-3 text-sm text-amber-700">Konfirmasi baru bisa dilakukan setelah estimasi 2 hari tercapai.</p>
                    @endif

                    @if ($order->status === 'Selesai' && $order->received_at)
                        <p class="mt-3 text-sm font-semibold text-emerald-700">Pesanan sudah selesai pada {{ $order->received_at->format('d M Y, H:i') }}.</p>
                    @endif

                    @if ($order->status !== 'Selesai' && $order->payment_method !== 'COD' && in_array($order->midtrans_transaction_status, ['pending', 'deny', 'cancel', 'expire'], true))
                        <form action="{{ route('orders.pay', $order) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Bayar Ulang</button>
                        </form>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('orders.invoice.download', $order) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                            Download Invoice
                        </a>
                    </div>

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
