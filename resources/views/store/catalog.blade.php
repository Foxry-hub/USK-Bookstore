@extends('layouts.app')

@section('content')
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

        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($books as $book)
                <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg">
                    <a href="{{ route('store.show', $book) }}" class="block overflow-hidden">
                        <img src="{{ $book->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-52 w-full object-cover transition group-hover:scale-105">
                    </a>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">{{ $book->category->name }}</p>
                        <a href="{{ route('store.show', $book) }}" class="mt-2 line-clamp-2 text-base font-bold text-slate-900 hover:text-brand-500">{{ $book->title }}</a>
                        <p class="mt-1 text-sm text-slate-600">{{ $book->author }}</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-900">Rp {{ number_format($book->price, 0, ',', '.') }}</p>

                        @auth
                            @if (!auth()->user()->isAdmin())
                                <form action="{{ route('cart.add', $book) }}" method="POST" class="mt-3" data-cart-add>
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Tambah ke Keranjang</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="mt-3 block rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Login untuk beli</a>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada buku yang cocok sama kata kunci kamu.
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $books->links() }}</div>
    </section>
@endsection
