@extends('layouts.app')

@section('content')
    <style>
        .catalog-scroll-track {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .catalog-scroll-track::-webkit-scrollbar {
            display: none;
        }
    </style>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm lg:p-10">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Katalog Lengkap</p>
                <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Temukan buku untuk semua kebutuhan.</h1>
                <p class="mt-2 text-sm text-slate-600">Filter berdasarkan kategori atau cari judul dan penulis favoritmu.</p>
            </div>
            <a href="{{ route('store.index') }}#catalog" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition-all duration-300 ease-in-out hover:border-slate-900 hover:bg-slate-900 hover:text-white">Kembali ke Landing</a>
        </div>

        <form method="GET" action="{{ route('store.catalog') }}" class="mt-6 grid gap-3 md:grid-cols-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul / penulis..." class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
            <select name="category" class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">Cari Buku</button>
        </form>

        <div class="mt-5 flex flex-wrap items-center gap-2 text-xs text-slate-600">
            <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold">Total hasil: {{ $booksCount }} buku</span>
            @if (request('category'))
                <a href="{{ route('store.catalog', array_filter(['search' => request('search')])) }}" class="rounded-full border border-slate-300 px-3 py-1 font-semibold text-slate-700 hover:bg-slate-50">Reset kategori</a>
            @endif
        </div>

        <div class="mt-8 space-y-8">
            @if ($activeCategoryId)
                <div class="rounded-2xl border border-brand-100 bg-brand-50 px-4 py-3 text-sm text-brand-700">
                    Sedang menampilkan kategori: <span class="font-bold">{{ $activeCategoryName }}</span>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Tiap kategori ditampilkan dalam 1 baris. Geser ke kanan atau kiri untuk lihat buku lainnya.
                </div>
            @endif

            @forelse ($groupedCategories as $categoryGroup)
                <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                    @php
                        $trackId = 'category-scroll-' . $loop->index;
                    @endphp

                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-slate-900">{{ $categoryGroup['name'] }}</h2>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $categoryGroup['books']->count() }} buku</span>
                        </div>

                        @if ($activeCategoryId)
                            <a href="{{ route('store.catalog', array_filter(['search' => request('search')])) }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900">Kembali ke katalog</a>
                        @elseif ($categoryGroup['id'])
                            <a href="{{ route('store.catalog', array_filter(['category' => $categoryGroup['id'], 'search' => request('search')])) }}" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900">Lihat lebih jelas</a>
                        @endif
                    </div>

                    @if ($activeCategoryId)
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($categoryGroup['books'] as $book)
                                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg">
                                    <a href="{{ route('store.show', $book) }}" class="block overflow-hidden">
                                        <img src="{{ $book->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-52 w-full object-cover transition group-hover:scale-105">
                                    </a>
                                    <div class="flex flex-1 flex-col p-4">
                                        <a href="{{ route('store.show', $book) }}" class="line-clamp-2 text-base font-bold text-slate-900 hover:text-brand-500">{{ $book->title }}</a>
                                        <p class="mt-1 text-sm text-slate-600">{{ $book->author }}</p>
                                        <p class="mt-3 text-lg font-extrabold text-slate-900">Rp {{ number_format($book->price, 0, ',', '.') }}</p>

                                        @auth
                                            @if (!auth()->user()->isAdmin())
                                                <form action="{{ route('cart.add', $book) }}" method="POST" class="mt-auto pt-3" data-cart-add>
                                                    @csrf
                                                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Tambah ke Keranjang</button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="mt-auto block pt-3 text-center">
                                                <span class="block rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Login untuk beli</span>
                                            </a>
                                        @endauth
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="relative">
                            <button
                                type="button"
                                class="catalog-scroll-btn absolute left-2 top-1/2 z-10 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-sm transition hover:bg-white hover:text-slate-900"
                                data-scroll-target="{{ $trackId }}"
                                data-scroll-direction="left"
                                aria-label="Geser ke kiri"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="catalog-scroll-btn absolute right-2 top-1/2 z-10 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-sm transition hover:bg-white hover:text-slate-900"
                                data-scroll-target="{{ $trackId }}"
                                data-scroll-direction="right"
                                aria-label="Geser ke kanan"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                                </svg>
                            </button>

                            <div id="{{ $trackId }}" class="catalog-scroll-track overflow-x-auto px-1 pb-1 scroll-smooth">
                                <div class="grid min-w-full grid-flow-col auto-cols-[85%] gap-4 sm:auto-cols-[48%] lg:auto-cols-[31%] xl:auto-cols-[calc((100%-3rem)/4)]">
                                @foreach ($categoryGroup['books'] as $book)
                                <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg">
                                    <a href="{{ route('store.show', $book) }}" class="block overflow-hidden">
                                        <img src="{{ $book->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-52 w-full object-cover transition group-hover:scale-105">
                                    </a>
                                    <div class="flex flex-1 flex-col p-4">
                                        <a href="{{ route('store.show', $book) }}" class="line-clamp-2 text-base font-bold text-slate-900 hover:text-brand-500">{{ $book->title }}</a>
                                        <p class="mt-1 text-sm text-slate-600">{{ $book->author }}</p>
                                        <p class="mt-3 text-lg font-extrabold text-slate-900">Rp {{ number_format($book->price, 0, ',', '.') }}</p>

                                        @auth
                                            @if (!auth()->user()->isAdmin())
                                                <form action="{{ route('cart.add', $book) }}" method="POST" class="mt-auto pt-3" data-cart-add>
                                                    @csrf
                                                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Tambah ke Keranjang</button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="mt-auto block pt-3 text-center">
                                                <span class="block rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Login untuk beli</span>
                                            </a>
                                        @endauth
                                    </div>
                                </article>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada buku yang cocok sama kata kunci kamu.
                </div>
            @endforelse
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.catalog-scroll-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const targetId = button.dataset.scrollTarget;
                    const direction = button.dataset.scrollDirection;

                    if (!targetId || !direction) {
                        return;
                    }

                    const track = document.getElementById(targetId);
                    if (!track) {
                        return;
                    }

                    const scrollStep = Math.max(track.clientWidth * 0.8, 260);
                    track.scrollBy({
                        left: direction === 'left' ? -scrollStep : scrollStep,
                        behavior: 'smooth',
                    });
                });
            });
        });
    </script>
@endsection
