@extends('layouts.app')

@section('content')
    <section data-focus-order="{{ (string) request('focus_order', '') }}">
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

                    $statusSteps = [
                        'Menunggu Konfirmasi' => 1,
                        'Menunggu Verifikasi' => 1,
                        'Dibayar' => 1,
                        'Diproses' => 2,
                        'Dikirim' => 3,
                        'Selesai' => 4,
                        'Pembayaran Gagal' => 4,
                        'Refund' => 4,
                    ];

                    $currentStep = $statusSteps[$order->status] ?? 1;
                    $progressWidthClass = match ($currentStep) {
                        1 => 'w-[12.5%]',
                        2 => 'w-[37.5%]',
                        3 => 'w-[62.5%]',
                        default => 'w-[87.5%]',
                    };
                    $finalStepLabel = $order->status === 'Pembayaran Gagal' ? 'Dibatalkan' : 'Selesai';
                    $progressStepLabels = ['Order Dibuat', 'Diproses', 'Dikirim', $finalStepLabel];
                    $paymentMethodLabel = match (strtoupper((string) $order->payment_method)) {
                        'MIDTRANS' => 'Credit Card / Debit',
                        'COD' => 'COD',
                        default => $order->payment_method,
                    };

                    $groupedItems = $order->items
                        ->groupBy(fn ($item) => (string) ($item->book_id ?? ('missing-' . $item->id)))
                        ->map(function ($items) {
                            $firstItem = $items->first();
                            $book = $firstItem?->book;

                            return [
                                'title' => $book?->title ?? 'Buku tidak ditemukan',
                                'image' => $book?->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80',
                                'quantity' => (int) $items->sum('quantity'),
                                'subtotal' => (float) $items->sum('subtotal'),
                            ];
                        })
                        ->values();
                @endphp
                <details
                    class="group order-detail rounded-2xl border border-slate-200 bg-white shadow-sm {{ (string) request('focus_order') === (string) $order->id ? 'ring-2 ring-emerald-200' : '' }}"
                    id="order-{{ $order->id }}"
                    data-order-id="{{ $order->id }}"
                    @if ((string) request('open_order') === (string) $order->id) open @endif
                >
                    <summary class="list-none cursor-pointer p-5 [&::-webkit-details-marker]:hidden">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                                <p class="mt-1 text-xs text-slate-500">Order Date: {{ $order->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                                <span class="inline-flex items-center gap-1 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition group-hover:bg-slate-50">
                                    View Order
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                <span>Produk Dibeli</span>
                                <span>{{ $paymentMethodLabel }}</span>
                            </div>

                            <div class="space-y-2">
                                @foreach ($groupedItems as $groupedItem)
                                    <div class="flex items-start gap-3 rounded-lg bg-white p-2.5">
                                        <img src="{{ $groupedItem['image'] }}" alt="{{ $groupedItem['title'] }}" class="h-12 w-12 rounded-md object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $groupedItem['title'] }}</p>
                                            <p class="text-xs text-slate-500">x{{ $groupedItem['quantity'] }}</p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800">Rp {{ number_format($groupedItem['subtotal'], 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-sm">
                                <span class="font-semibold text-slate-600">Total Belanja</span>
                                <span class="text-lg font-extrabold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </summary>

                    <div class="order-detail-content border-t border-slate-100 p-5">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-800">Progress Pesanan</p>
                            <div class="relative mt-3">
                                <div class="h-1 rounded-full bg-slate-200"></div>
                                <div class="absolute left-0 top-0 h-1 rounded-full bg-emerald-500 transition-all duration-300 {{ $progressWidthClass }}"></div>
                            </div>
                            <div class="mt-3 grid grid-cols-4 gap-2 text-center text-xs">
                                @foreach ($progressStepLabels as $index => $stepLabel)
                                    @php $stepNumber = $index + 1; @endphp
                                    <div>
                                        <span class="mx-auto inline-flex h-7 w-7 items-center justify-center rounded-full text-[11px] font-bold {{ $currentStep >= $stepNumber ? 'bg-emerald-600 text-white' : 'bg-white text-slate-500 border border-slate-300' }}">{{ $stepNumber }}</span>
                                        <p class="mt-1 {{ $currentStep >= $stepNumber ? 'font-semibold text-slate-900' : 'text-slate-500' }}">{{ $stepLabel }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-[1.4fr_1fr]">
                            <div class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-800">Daftar Barang</p>
                                <div class="mt-3 space-y-3">
                                    @foreach ($groupedItems as $groupedItem)
                                        <div class="flex items-start gap-3 rounded-lg border border-slate-100 p-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $groupedItem['title'] }}</p>
                                                <p class="text-xs text-slate-500">Qty: {{ $groupedItem['quantity'] }}</p>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-800">Rp {{ number_format($groupedItem['subtotal'], 0, ',', '.') }}</p>
                                        </div>
                                    @endforeach

                                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-sm">
                                        <span class="font-semibold text-slate-600">Total</span>
                                        <span class="text-lg font-extrabold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="rounded-xl border border-slate-200 bg-white p-4">
                                    <p class="text-sm font-semibold text-slate-800">Order Summary</p>
                                    <div class="mt-3 space-y-2 text-sm">
                                        <div class="flex items-center justify-between text-slate-600">
                                            <span>Subtotal</span>
                                            <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-600">
                                            <span>Total</span>
                                            <span class="font-bold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                                    <p><span class="font-semibold text-slate-800">Metode Bayar:</span> {{ $paymentMethodLabel }}</p>
                                    <p><span class="font-semibold text-slate-800">Alamat:</span> {{ $order->shipping_address }}</p>
                                    <p class="mt-1"><span class="font-semibold text-slate-800">No. Telp:</span> {{ $order->phone }}</p>
                                    @if ($order->midtrans_transaction_status)
                                        <p class="mt-2">
                                            <span class="font-semibold text-slate-800">Status Payment:</span>
                                            <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $paymentStatusClasses[$order->midtrans_transaction_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->midtrans_transaction_status }}</span>
                                        </p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('orders.invoice.download', $order) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Download Invoice</a>

                                    @if ($order->status !== 'Selesai' && $order->payment_method !== 'COD' && in_array($order->midtrans_transaction_status, ['pending', 'deny', 'cancel', 'expire'], true))
                                        <form action="{{ route('orders.pay', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Bayar Ulang</button>
                                        </form>
                                    @endif

                                    @if ($order->payment_method === 'COD' && $order->status === 'Dikirim' && $order->estimated_delivery_at && now()->greaterThanOrEqualTo($order->estimated_delivery_at) && ! $order->received_at)
                                        <form action="{{ route('orders.confirm-received', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Diterima</button>
                                        </form>

                                        <form action="{{ route('orders.confirm-not-received', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white">Tidak Diterima</button>
                                        </form>
                                    @endif

                                    @if ($order->payment_method !== 'COD' && $order->status === 'Dikirim' && $order->estimated_delivery_at && now()->greaterThanOrEqualTo($order->estimated_delivery_at) && ! $order->received_at)
                                        <form action="{{ route('orders.confirm-received', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Konfirmasi Diterima</button>
                                        </form>
                                    @endif
                                </div>

                                @if ($order->status === 'Dikirim' && $order->estimated_delivery_at && now()->lessThan($order->estimated_delivery_at))
                                    <p class="text-sm text-amber-700">Konfirmasi baru bisa dilakukan setelah estimasi 2 hari tercapai.</p>
                                @endif

                                @if ($order->status === 'Selesai' && $order->received_at)
                                    <p class="text-sm font-semibold text-emerald-700">Pesanan selesai pada {{ $order->received_at->format('d M Y, H:i') }}.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </details>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada pesanan yang dibuat.
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var orderDetails = document.querySelectorAll('.order-detail');
            var ordersSection = document.querySelector('section[data-focus-order]');
            var focusedOrderId = ordersSection ? (ordersSection.getAttribute('data-focus-order') || '') : '';

            if (focusedOrderId !== '') {
                var focusedOrder = document.querySelector('[data-order-id="' + focusedOrderId + '"]');

                if (focusedOrder) {
                    var nav = document.querySelector('nav.sticky');
                    var navHeight = nav ? nav.offsetHeight : 0;
                    var focusOffset = 12;
                    var targetY = window.scrollY + focusedOrder.getBoundingClientRect().top - navHeight - focusOffset;

                    window.scrollTo({
                        top: Math.max(targetY, 0),
                        behavior: 'smooth',
                    });
                }
            }

            orderDetails.forEach(function (detail) {
                detail.addEventListener('toggle', function () {
                    if (!detail.open) {
                        return;
                    }

                    var detailContent = detail.querySelector('.order-detail-content');

                    if (!detailContent) {
                        return;
                    }

                    var nav = document.querySelector('nav.sticky');
                    var navHeight = nav ? nav.offsetHeight : 0;
                    var extraOffset = 16;
                    var targetY = window.scrollY + detailContent.getBoundingClientRect().top - navHeight - extraOffset;

                    window.scrollTo({
                        top: Math.max(targetY, 0),
                        behavior: 'smooth',
                    });
                });
            });
        });
    </script>
@endsection
