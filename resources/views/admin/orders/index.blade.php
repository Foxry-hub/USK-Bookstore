@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-2xl font-bold text-slate-900">List Pesanan User</h1>

        <div class="mt-4 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                            <p class="text-sm text-slate-600">Pemesan: {{ $order->user->name }} ({{ $order->user->email }})</p>
                            <p class="text-sm text-slate-600">Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>

                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai'] as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Update</button>
                        </form>
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
