@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        @php
            $unreadContactMessages = \App\Models\ContactMessage::where('is_read', false)->count();
        @endphp

        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-blue-900 to-emerald-800 px-6 py-8 text-white shadow-lg sm:px-8">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-12 right-20 h-36 w-36 rounded-full bg-white/10"></div>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-100">Admin Panel</p>
            <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">Dashboard BookStore</h1>
            <p class="mt-3 max-w-2xl text-sm text-blue-50/90">
                Ringkasan cepat supaya kamu bisa mantau performa toko dan langsung ambil aksi tanpa muter-muter halaman.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Kategori</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalCategories }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Buku</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalBooks }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total User</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalUsers }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Pesanan</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $totalOrders }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Pesanan Terbaru</h2>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-600">Lihat semua</a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($recentOrders as $order)
                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-semibold text-slate-900">{{ $order->order_code }}</p>
                                <span class="w-fit rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">{{ $order->status }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">{{ $order->user->name }} • Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                            Belum ada order terbaru.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Aksi Cepat</h2>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('admin.categories.create') }}" class="block rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">+ Tambah Kategori</a>
                    <a href="{{ route('admin.books.create') }}" class="block rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white">+ Tambah Buku</a>
                    <a href="{{ route('admin.messages.index') }}" class="flex items-center justify-between rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">
                        <span>Lihat Pesan Masuk</span>
                        @if ($unreadContactMessages > 0)
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">{{ $unreadContactMessages }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">Lihat User</a>
                    <a href="{{ route('admin.orders.index') }}" class="block rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">Kelola Pesanan</a>
                </div>
            </div>
        </div>
    </section>
@endsection
