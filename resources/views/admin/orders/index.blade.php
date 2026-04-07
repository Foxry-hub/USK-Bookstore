@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-2xl font-bold text-slate-900">List Pesanan User</h1>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-3">
            <div>
                <label for="payment_status" class="mb-1 block text-xs font-semibold text-slate-700">Filter Status Pembayaran</label>
                <select id="payment_status" name="payment_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="all" @selected(($paymentStatus ?? 'all') === 'all')>Semua</option>
                    <option value="cod" @selected(($paymentStatus ?? 'all') === 'cod')>COD</option>
                    <option value="pending" @selected(($paymentStatus ?? 'all') === 'pending')>Midtrans Pending</option>
                    <option value="settlement" @selected(($paymentStatus ?? 'all') === 'settlement')>Midtrans Settlement</option>
                    <option value="capture" @selected(($paymentStatus ?? 'all') === 'capture')>Midtrans Capture</option>
                    <option value="deny" @selected(($paymentStatus ?? 'all') === 'deny')>Midtrans Deny</option>
                    <option value="cancel" @selected(($paymentStatus ?? 'all') === 'cancel')>Midtrans Cancel</option>
                    <option value="expire" @selected(($paymentStatus ?? 'all') === 'expire')>Midtrans Expire</option>
                    <option value="refund" @selected(($paymentStatus ?? 'all') === 'refund')>Midtrans Refund</option>
                    <option value="partial_refund" @selected(($paymentStatus ?? 'all') === 'partial_refund')>Midtrans Partial Refund</option>
                </select>
            </div>

            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Terapkan Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Reset</a>
        </form>

        <div class="mt-4 space-y-4">
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
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                            <p class="text-sm text-slate-600">Pemesan: {{ $order->user->name }} ({{ $order->user->email }})</p>
                            <p class="text-sm text-slate-600">Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            <div class="mt-1 text-sm text-slate-600">
                                <span>Status Order:</span>
                                <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->status }}</span>
                            </div>
                            @if ($order->payment_method === 'COD')
                                <div class="mt-1 text-sm text-slate-600">
                                    <span>Status Pembayaran:</span>
                                    <span class="ml-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">COD</span>
                                </div>
                            @elseif ($order->midtrans_transaction_status)
                                <div class="mt-1 text-sm text-slate-600">
                                    <span>Status Pembayaran:</span>
                                    <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $paymentStatusClasses[$order->midtrans_transaction_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->midtrans_transaction_status }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($order->status === 'Menunggu Konfirmasi' || $order->status === 'Dibayar' || $order->status === 'Pembayaran Gagal')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Diproses">
                                    <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-cyan-700">Diproses</button>
                                </form>
                            @endif

                            @if ($order->status === 'Diproses')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Dikirim">
                                    <button type="submit" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Dikirim</button>
                                </form>
                            @endif

                            @if ($order->status === 'Dikirim')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Selesai">
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Tandai Selesai</button>
                                </form>
                            @endif

                            @if ($order->status === 'Selesai')
                                <span class="inline-flex cursor-default rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Selesai</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                        <p>Shipped at: <span class="font-semibold text-slate-900">{{ $order->shipped_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                        <p>Estimasi sampai: <span class="font-semibold text-slate-900">{{ $order->estimated_delivery_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                        <p>Selesai: <span class="font-semibold text-slate-900">{{ $order->received_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-50 p-3">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between py-1 text-sm text-slate-700">
                                <span>{{ $item->book->title }} x {{ $item->quantity }}</span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada pesanan masuk.
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    </section>
@endsection
