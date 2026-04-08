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

            </div>

            <div class="rounded-3xl border border-white/20 bg-white/10 p-3 backdrop-blur-sm">
                <img src="https://images.unsplash.com/photo-1524578271613-d550eacf6090?auto=format&fit=crop&w=1200&q=80" alt="Rak buku estetik" class="h-80 w-full rounded-2xl object-cover sm:h-96">
            </div>
        </div>
    </section>

    <section id="catalog" style="scroll-margin-top: 6.5rem;" class="mt-20 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:mt-24">
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

        <div class="relative mt-6 sm:mt-6">
            <div id="landingCatalog" class="no-scrollbar flex snap-x snap-mandatory gap-0 overflow-x-auto px-0 scroll-smooth pb-2 sm:grid sm:snap-none sm:overflow-visible sm:gap-5 sm:pb-0 sm:grid-cols-2 xl:grid-cols-4">
            @forelse ($featuredBooks as $book)
                <article data-catalog-slide="{{ $loop->index }}" class="group basis-full min-w-full max-w-full snap-start overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg sm:basis-auto sm:min-w-0 sm:max-w-none cursor-pointer">
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

            <div class="pointer-events-none absolute inset-y-0 left-0 right-0 flex items-center justify-between sm:hidden" aria-hidden="true">
                <button
                    type="button"
                    onclick="var c=document.getElementById('landingCatalog'); c.scrollBy({ left: -c.clientWidth, behavior: 'smooth' })"
                    class="pointer-events-auto ml-1 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white/95 text-slate-700 shadow transition hover:bg-slate-900 hover:text-white"
                    aria-label="Geser katalog ke kiri"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M11.79 14.77a.75.75 0 0 1-1.06.02l-4.25-4a.75.75 0 0 1 0-1.08l4.25-4a.75.75 0 1 1 1.04 1.08L8.06 10l3.73 3.23a.75.75 0 0 1 .02 1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <button
                    type="button"
                    onclick="var c=document.getElementById('landingCatalog'); c.scrollBy({ left: c.clientWidth, behavior: 'smooth' })"
                    class="pointer-events-auto mr-1 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white/95 text-slate-700 shadow transition hover:bg-slate-900 hover:text-white"
                    aria-label="Geser katalog ke kanan"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M8.21 5.23a.75.75 0 0 1 1.06-.02l4.25 4a.75.75 0 0 1 0 1.08l-4.25 4a.75.75 0 1 1-1.04-1.08L11.94 10 8.21 6.77a.75.75 0 0 1-.02-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            @if ($featuredBooks->count() > 1)
                <div id="landingCatalogDots" class="mt-4 flex items-center justify-center gap-2 sm:hidden" aria-label="Indikator slide katalog">
                    @foreach ($featuredBooks as $book)
                        <button
                            type="button"
                            data-catalog-dot="{{ $loop->index }}"
                            data-slide-index="{{ $loop->index }}"
                            onclick="var c=document.getElementById('landingCatalog'); c.scrollTo({ left: c.clientWidth * Number(this.dataset.slideIndex), behavior: 'smooth' })"
                            class="h-2.5 w-2.5 rounded-full transition {{ $loop->first ? 'bg-slate-900' : 'bg-slate-300' }}"
                            aria-label="Buka slide {{ $loop->iteration }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-7 flex justify-center sm:justify-end">
            <a href="{{ route('store.catalog') }}" class="w-full max-w-xs rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 transition-all duration-300 ease-in-out hover:border-slate-900 hover:bg-slate-900 hover:text-white sm:w-auto">Lihat Katalog Lengkap</a>
        </div>
    </section>

    <section id="about" style="scroll-margin-top: 6.5rem;" class="mt-12 rounded-[2rem] border border-slate-200 bg-white p-7 shadow-sm lg:p-10">
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

    <section id="contact" style="scroll-margin-top: 6.5rem;" class="mt-10 rounded-[2rem] border border-slate-200 bg-[#d7d7d7] p-4 shadow-sm sm:p-6 lg:p-8">
        <div class="rounded-[1.6rem] border border-white/80 bg-[#f7f7f7] p-6 lg:p-8">
            <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
                <div>
                    <h2 class="text-4xl font-semibold tracking-tight text-slate-900">Get in touch</h2>

                    <div class="mt-7 space-y-5 text-sm text-slate-700">
                        <div>
                            <p class="text-slate-500">Email:</p>
                            <a href="mailto:admin@bookstore.com" class="mt-1 inline-block font-medium text-slate-900 hover:text-slate-600">admin@bookstore.com</a>
                        </div>

                        <div>
                            <p class="text-slate-500">Phone:</p>
                            <a href="tel:+6281388088171" class="mt-1 inline-block font-medium text-slate-900 hover:text-slate-600">+62 813 8808 8171</a>
                        </div>

                        <div>
                            <p class="text-slate-500">Address:</p>
                            <p class="mt-1 max-w-xs font-medium text-slate-900">Jl. Innovation Avenue No. 123, Jakarta Timur, Indonesia</p>
                        </div>

                        <div>
                            <p class="mb-2 text-slate-500">Follow us:</p>
                            <div class="flex items-center gap-2">
                                <a href="#" aria-label="Instagram" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white transition hover:bg-slate-700">IG</a>
                                <a href="#" aria-label="Facebook" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white transition hover:bg-slate-700">FB</a>
                                <a href="#" aria-label="LinkedIn" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white transition hover:bg-slate-700">IN</a>
                                <a href="#" aria-label="X" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white transition hover:bg-slate-700">X</a>
                            </div>
                        </div>
                    </div>
                </div>

                @auth
                    @if (! auth()->user()->isAdmin())
                        <form class="space-y-4" method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <label class="block text-sm font-medium text-slate-700">
                                Your Name
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Your full name" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-200/60 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-slate-400 focus:outline-none">
                                @error('name')
                                    <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <label class="block text-sm font-medium text-slate-700">
                                Message
                                <textarea rows="6" name="message" placeholder="Write something..." class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-200/60 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-slate-400 focus:outline-none">{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>
                                @enderror
                            </label>

                            <button type="submit" class="w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Send Message</button>
                        </form>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-8 text-sm text-slate-600">
                            Akun admin tidak bisa mengirim pesan kontak.
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-slate-300 bg-white px-4 py-8 text-sm text-slate-600">
                        Silakan login dulu untuk mengirim pesan ke admin.
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Login</a>
                            <a href="{{ route('register') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Register</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </section>

    <footer class="mt-10 rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="inline-flex items-center gap-2 text-lg font-extrabold text-slate-900">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 bg-white">
                        <img src="{{ asset('assets/logo.png') }}" alt="Logo BookStore" class="h-4 w-4 object-contain">
                    </span>
                    <span>BookStore</span>
                </p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var catalog = document.getElementById('landingCatalog');
            var dotsWrap = document.getElementById('landingCatalogDots');

            if (!catalog || !dotsWrap) {
                return;
            }

            var dots = dotsWrap.querySelectorAll('[data-catalog-dot]');

            var updateActiveDot = function () {
                var slideWidth = catalog.clientWidth || 1;
                var index = Math.round(catalog.scrollLeft / slideWidth);

                dots.forEach(function (dot, dotIndex) {
                    var isActive = dotIndex === index;
                    dot.classList.toggle('bg-slate-900', isActive);
                    dot.classList.toggle('w-6', isActive);
                    dot.classList.toggle('bg-slate-300', !isActive);
                    dot.classList.toggle('w-2.5', !isActive);
                });
            };

            catalog.addEventListener('scroll', updateActiveDot, { passive: true });
            window.addEventListener('resize', updateActiveDot);
            updateActiveDot();
        });
    </script>
@endsection
