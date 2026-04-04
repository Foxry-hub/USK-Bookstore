@extends('layouts.app')

@section('content')
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 p-8 text-white shadow-xl lg:p-12">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-emerald-300/20 blur-3xl"></div>

        <div class="relative grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="inline-flex rounded-full border border-white/30 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100">BookStore Online</p>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl">Belanja buku sekarang terasa lebih cepat, modern, dan tetap nyaman.</h1>
                <p class="mt-4 max-w-xl text-base text-slate-200">Temukan buku favorit, simpan ke keranjang, lalu checkout tanpa ribet. Semua dibuat responsif supaya enak dipakai dari HP sampai desktop.</p>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#catalog" class="rounded-xl bg-cyan-400 px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-cyan-300">Lihat Selengkapnya</a>
                    <a href="#about" class="rounded-xl border border-white/40 px-5 py-3 text-sm font-semibold text-white/95 transition hover:bg-white/10">Tentang Kami</a>
                </div>

                <div class="mt-8 grid max-w-md grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 text-center backdrop-blur">
                        <p class="text-2xl font-extrabold">{{ $totalBooks }}</p>
                        <p class="text-xs text-slate-200">Total Buku</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-3 text-center backdrop-blur">
                        <p class="text-2xl font-extrabold">{{ $categoriesCount }}</p>
                        <p class="text-xs text-slate-200">Kategori</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/20 bg-white/10 p-3 backdrop-blur-sm">
                <img src="https://images.unsplash.com/photo-1524578271613-d550eacf6090?auto=format&fit=crop&w=1200&q=80" alt="Rak buku estetik" class="h-80 w-full rounded-2xl object-cover sm:h-96">
            </div>
        </div>
    </section>

    <section id="catalog" style="scroll-margin-top: 6.5rem;" class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Katalog Buku</h2>
                <p class="text-sm text-slate-600">Pencarian cepat buat kamu yang langsung tahu judul buku.</p>
            </div>
            <form method="GET" action="{{ route('store.catalog') }}" class="flex w-full flex-col gap-3 sm:flex-row lg:max-w-2xl">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul / penulis..." class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none">
                <button type="submit" class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white sm:whitespace-nowrap">Cari</button>
            </form>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($featuredBooks as $book)
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
                                <form action="{{ route('cart.add', $book) }}" method="POST" class="mt-3" data-cart-add>
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

        <div class="mt-7 flex justify-center sm:justify-end">
            <a href="{{ route('store.catalog') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition-all duration-300 ease-in-out hover:border-slate-900 hover:bg-slate-900 hover:text-white">Lihat Katalog Lengkap</a>
        </div>
    </section>

    <section id="about" class="mt-12 rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm lg:p-10">
        <div class="grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">About Us</p>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Toko buku digital yang fokus ke pengalaman belanja yang ramah dan hidup.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base">BookStore hadir buat bikin belanja buku terasa lebih santai. Kami kurasi koleksi buku populer, pengembangan diri, dan referensi belajar yang relevan buat pelajar, mahasiswa, maupun pekerja. Fokus kami sederhana: pilihan jelas, proses cepat, dan tampilan yang tidak membosankan.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700">Kurasi Berkualitas</span>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700">Checkout Mudah</span>
                    <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700">Responsif Semua Perangkat</span>
                </div>
            </div>

            <div class="rounded-3xl bg-slate-900 p-6 text-slate-100 sm:p-7">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Misi Kami</p>
                <p class="mt-3 text-sm leading-7 text-slate-200">Membuat akses buku yang bagus jadi lebih dekat untuk semua orang, dengan layanan yang cepat dan pengalaman yang menyenangkan dari klik pertama sampai checkout selesai.</p>
                <div class="mt-5 border-t border-white/15 pt-4">
                    <p class="text-sm font-semibold">Pelayanan</p>
                    <p class="text-xs text-slate-300">Senin - Sabtu, 08.00 - 21.00 WIB</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="mt-10 rounded-[2rem] border border-slate-800 bg-slate-950 p-7 text-slate-100 shadow-sm lg:p-10">
        <div class="grid gap-8 lg:grid-cols-[1.15fr_1fr] lg:items-center">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Kontak Admin</p>
                <h2 class="mt-2 text-3xl font-extrabold text-white">Hubungi admin tanpa ribet.</h2>
                <p class="mt-3 text-sm leading-7 text-slate-300">Kalau ada pertanyaan tentang pesanan, checkout, atau rekomendasi buku, langsung hubungi kami. Tim admin akan bantu secepat mungkin di jam operasional.</p>
            </div>

            <div class="p-2 sm:p-3">
                <div class="space-y-4 text-sm text-slate-100">
                    <a href="mailto:admin@bookstore.com" class="flex items-center gap-3 py-1 font-semibold text-slate-100 transition hover:text-cyan-300">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-800 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9m19.5 0A2.25 2.25 0 0 0 19.5 5.25h-15A2.25 2.25 0 0 0 2.25 7.5m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615a2.25 2.25 0 0 1-1.07-1.916V7.5" />
                            </svg>
                        </span>
                        <span>admin@bookstore.com</span>
                    </a>

                    <a href="https://wa.me/6281388088171" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 py-1 font-semibold text-slate-100 transition hover:text-emerald-300">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-5 w-5">
                                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.03 2 9.96 9.96 0 0 0 2.07 11.96c0 1.75.46 3.47 1.33 4.98L2 22l5.2-1.36a9.94 9.94 0 0 0 4.75 1.2h.01c5.5 0 9.96-4.46 9.96-9.96a9.9 9.9 0 0 0-2.87-6.97Zm-7.09 15.25h-.01a8.26 8.26 0 0 1-4.21-1.16l-.3-.18-3.08.8.82-3-.2-.31a8.24 8.24 0 0 1-1.27-4.38c0-4.57 3.72-8.29 8.3-8.29a8.2 8.2 0 0 1 5.87 2.44 8.22 8.22 0 0 1 2.42 5.85c0 4.58-3.72 8.3-8.29 8.3Zm4.55-6.2c-.25-.13-1.47-.72-1.7-.8-.23-.08-.4-.12-.57.12-.17.25-.65.8-.8.96-.15.17-.29.19-.54.06-.25-.13-1.04-.38-1.98-1.2-.73-.65-1.22-1.45-1.36-1.7-.14-.25-.01-.38.11-.5.11-.1.25-.29.37-.43.12-.14.16-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.57-1.37-.78-1.87-.2-.48-.4-.42-.57-.43h-.49c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1s.9 2.43 1.02 2.6c.12.17 1.76 2.68 4.27 3.76.6.26 1.08.42 1.44.54.6.19 1.14.16 1.57.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29Z"/>
                            </svg>
                        </span>
                        <span>+62 813-8808-8171</span>
                    </a>

                    <p class="pt-2 text-sm text-slate-300">Alamat: <span class="font-semibold text-white"> Jakarta Timur, Indonesia</span></p>
                </div>
            </div>
        </div>
    </section>

    <footer class="mt-10 rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-lg font-extrabold text-slate-900">BookStore</p>
                <p class="text-sm text-slate-600">Belanja buku modern, cepat, dan nyaman.</p>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm font-medium text-slate-600">
                <a href="#catalog" class="transition hover:text-slate-900">Katalog</a>
                <a href="#about" class="transition hover:text-slate-900">About Us</a>
                <a href="#contact" class="transition hover:text-slate-900">Kontak</a>
            </div>
        </div>
        <div class="mt-5 border-t border-slate-200 pt-4 text-xs text-slate-500">
            &copy; {{ date('Y') }} BookStore. All rights reserved.
        </div>
    </footer>
@endsection
