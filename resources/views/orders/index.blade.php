@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-3xl font-bold text-slate-900">Riwayat Pesanan Saya</h1>
        <p class="mt-2 text-sm text-slate-600">Semua pesanan COD kamu ada di sini.</p>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                        <span class="w-fit rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-600">{{ $order->status }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">Total: <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></p>
                    <p class="text-sm text-slate-600">Metode Bayar: {{ $order->payment_method }}</p>

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
