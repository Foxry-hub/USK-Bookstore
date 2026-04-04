@extends('layouts.app')

@section('content')
    <section class="grid gap-8 rounded-3xl bg-white p-8 shadow-sm lg:grid-cols-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-500">Bookstore Sederhana</p>
            <h1 class="mt-3 text-4xl font-extrabold leading-tight text-slate-900">Cari buku favoritmu, checkout gampang, bayar saat barang sampai.</h1>
            <p class="mt-4 text-base text-slate-600">Kita fokus ke pengalaman belanja buku yang clean, cepat, dan nyaman dipakai di HP maupun laptop.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="#catalog" class="rounded-xl bg-brand-500 px-5 py-3 text-sm font-semibold text-white">Lihat Katalog</a>
                <a href="#about" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Tentang Kami</a>
            </div>
        </div>
        <div class="rounded-2xl bg-slate-100 p-4">
            <img src="https://images.unsplash.com/photo-1524578271613-d550eacf6090?auto=format&fit=crop&w=1200&q=80" alt="Rak buku estetik" class="h-72 w-full rounded-2xl object-cover">
        </div>
    </section>

    <section id="catalog" class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Katalog Buku</h2>
                <p class="text-sm text-slate-600">Gunakan pencarian buat nemuin judul atau penulis lebih cepat.</p>
            </div>
            <form method="GET" action="{{ route('store.index') }}" class="grid w-full gap-3 md:grid-cols-3 lg:max-w-3xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul / penulis..." class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
                <select name="category" class="rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">Cari Buku</button>
            </form>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($books as $book)
                <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white hover:shadow-lg transition cursor-pointer">
                    <a href="{{ route('store.show', $book) }}" class="block overflow-hidden">
                        <img src="{{ $book->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" alt="{{ $book->title }}" class="h-52 w-full object-cover group-hover:scale-105 transition">
                    </a>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">{{ $book->category->name }}</p>
                        <a href="{{ route('store.show', $book) }}" class="mt-2 line-clamp-2 text-base font-bold text-slate-900 hover:text-brand-500">{{ $book->title }}</a>
                        <p class="mt-1 text-sm text-slate-600">{{ $book->author }}</p>
                        <p class="mt-3 text-lg font-extrabold text-slate-900">Rp {{ number_format($book->price, 0, ',', '.') }}</p>

                        @auth
                            @if (!auth()->user()->isAdmin())
                                <form action="{{ route('cart.add', $book) }}" method="POST" class="mt-3">
                                    @csrf
                                    <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition">Tambah ke Keranjang</button>
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

    <section id="about" class="mt-10 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-900">About Us</h2>
            <p class="mt-3 text-sm leading-7 text-slate-600">BookStore hadir buat bikin belanja buku terasa lebih santai. Kita kurasi koleksi buku populer dan buku produktivitas yang relevan buat pelajar, mahasiswa, maupun pekerja.</p>
        </div>
        <div id="contact" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-bold text-slate-900">Kontak Admin</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li>Email: admin@bookstore.test</li>
                <li>WhatsApp: +62 812-3456-7890</li>
                <li>Alamat: Banda Aceh, Indonesia</li>
            </ul>
        </div>
    </section>
@endsection
