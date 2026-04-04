@extends('layouts.app')

@section('content')
    @php
        $fallbackImage = 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80';
        $galleryImages = $book->galleryImages();

        if (count($galleryImages) === 0) {
            $galleryImages = [$fallbackImage];
        }

    @endphp

    <div class="rounded-3xl bg-white p-8 shadow-sm">
        <!-- Main Content -->
        <div class="grid gap-10 lg:grid-cols-2">
            <!-- Left: Image Gallery -->
            <div class="flex flex-col gap-4">
                <div class="rounded-2xl bg-slate-100 overflow-hidden h-96 flex items-center justify-center">
                    <img
                        id="main-book-image"
                        src="{{ $galleryImages[0] }}"
                        alt="{{ $book->title }}" 
                        class="h-full w-full object-cover transition duration-500"
                    >
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($galleryImages as $image)
                        <button
                            type="button"
                            class="gallery-thumb group relative h-24 w-36 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100"
                            data-image="{{ $image }}"
                            data-index="{{ $loop->index }}"
                        >
                            <img src="{{ $image }}" alt="Thumbnail {{ $book->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                            <span class="pointer-events-none absolute inset-0 bg-black/30 opacity-0 transition duration-300 group-hover:opacity-100"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right: Product Info -->
            <div class="flex flex-col gap-6">
                <!-- Category & Title -->
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-500">{{ $book->category->name }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-900">{{ $book->title }}</h1>
                </div>

                <!-- Rating -->
                <div class="flex items-center gap-3">
                    <div class="flex gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 @if($i <= 4) text-yellow-400 @else text-slate-300 @endif fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-sm font-semibold text-slate-700">4.0 (24 reviews)</span>
                </div>

                <!-- Price & Availability -->
                <div class="space-y-3 border-y border-slate-200 py-4">
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-slate-900">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-green-700 font-semibold">Tersedia (25 stok)</span>
                    </div>
                </div>

                <!-- Author & Details -->
                <div class="space-y-2 text-sm">
                    <div class="flex gap-4">
                        <span class="font-semibold text-slate-700 w-24">Penulis:</span>
                        <span class="text-slate-600">{{ $book->author }}</span>
                    </div>
                    <div class="flex gap-4">
                        <span class="font-semibold text-slate-700 w-24">Kategori:</span>
                        <span class="text-slate-600">{{ $book->category->name }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-2">
                    @auth
                        @if (!auth()->user()->isAdmin())
                            <form action="{{ route('cart.add', $book) }}" method="POST" class="w-full" data-cart-add>
                                @csrf
                                <button type="submit" class="w-full rounded-xl bg-brand-500 px-6 py-3 font-semibold text-white hover:bg-brand-600 transition">
                                    Tambah ke Keranjang
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.books.edit', $book) }}" class="w-full rounded-xl bg-slate-900 px-6 py-3 text-center font-semibold text-white hover:bg-slate-800 transition">
                                Edit Buku
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="w-full rounded-xl border-2 border-slate-300 px-6 py-3 text-center font-semibold text-slate-700 hover:border-slate-400 transition">
                            Login untuk Beli
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Tabs: Description, Details -->
        <div class="mt-12 border-t border-slate-200 pt-8">
            <div class="flex gap-4 border-b border-slate-200">
                <button class="pb-3 px-2 font-semibold text-slate-900 border-b-2 border-brand-500 tab-btn" data-tab="deskripsi">Deskripsi</button>
                <button class="pb-3 px-2 font-semibold text-slate-600 hover:text-slate-900 tab-btn" data-tab="detail">Detail</button>
            </div>

            <!-- Deskripsi Content -->
            <div class="mt-6 space-y-4 tab-content" id="deskripsi">
                <p class="text-slate-700 leading-relaxed">{{ $book->description ?: 'Deskripsi belum tersedia untuk buku ini.' }}</p>
                
                <div class="rounded-lg bg-slate-50 p-4 mt-6">
                    <h3 class="font-semibold text-slate-900 mb-3">Informasi Penting</h3>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="flex gap-2">
                            <span class="text-brand-500">•</span>
                            <span>Kualitas premium dengan pengikat yang tahan lama</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-brand-500">•</span>
                            <span>Desain sampul dan tata letak yang indah</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-brand-500">•</span>
                            <span>Sempurna untuk koleksi pribadi atau hadiah</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Detail Content -->
            <div class="mt-6 space-y-4 tab-content hidden" id="detail">
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! nl2br(e($book->detail ?: 'Detail lengkap belum tersedia.')) !!}
                </div>
            </div>
        </div>

        <!-- Related Books -->
        @if ($relatedBooks->count() > 0)
            <div class="mt-12 pt-12 border-t border-slate-200">
                <div class="flex items-end justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Buku Pilihan Lainnya</h2>
                        <p class="text-sm text-slate-600">Buku lain dari kategori {{ $book->category->name }}</p>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedBooks as $relatedBook)
                        <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white hover:shadow-lg transition">
                            <a href="{{ route('store.show', $relatedBook) }}" class="block overflow-hidden">
                                <img 
                                    src="{{ $relatedBook->image_url ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80' }}" 
                                    alt="{{ $relatedBook->title }}" 
                                    class="h-52 w-full object-cover group-hover:scale-105 transition"
                                >
                            </a>
                            <div class="p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-brand-500">{{ $relatedBook->category->name }}</p>
                                <a href="{{ route('store.show', $relatedBook) }}" class="mt-2 line-clamp-2 text-base font-bold text-slate-900 hover:text-brand-500">
                                    {{ $relatedBook->title }}
                                </a>
                                <p class="mt-1 text-sm text-slate-600">{{ $relatedBook->author }}</p>
                                <p class="mt-3 text-lg font-extrabold text-slate-900">Rp {{ number_format($relatedBook->price, 0, ',', '.') }}</p>

                                @auth
                                    @if (!auth()->user()->isAdmin())
                                        <form action="{{ route('cart.add', $relatedBook) }}" method="POST" class="mt-3" data-cart-add>
                                            @csrf
                                            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition">
                                                Tambah ke Keranjang
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="mt-3 block rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">
                                        Login untuk beli
                                    </a>
                                @endauth
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Hide all tabs
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                });
                
                // Remove active state from all buttons
                document.querySelectorAll('.tab-btn').forEach(b => {
                    b.classList.remove('border-b-2', 'border-brand-500', 'text-slate-900', 'font-semibold');
                    b.classList.add('text-slate-600', 'hover:text-slate-900');
                });
                
                // Show selected tab
                document.getElementById(tabName).classList.remove('hidden');
                
                // Mark button as active
                this.classList.add('border-b-2', 'border-brand-500', 'text-slate-900', 'font-semibold');
                this.classList.remove('text-slate-600');
            });
        });

        const mainBookImage = document.getElementById('main-book-image');
        const galleryThumbs = document.querySelectorAll('.gallery-thumb');
        let currentIndex = 0;
        let slideInterval = null;

        const setMainImage = (nextImage, nextIndex) => {
            if (!nextImage || !mainBookImage) {
                return;
            }

            mainBookImage.style.opacity = '0.65';
            setTimeout(() => {
                mainBookImage.src = nextImage;
                mainBookImage.style.opacity = '1';
            }, 120);

            currentIndex = nextIndex;
        };

        const startAutoSlide = () => {
            if (galleryThumbs.length <= 1) {
                return;
            }

            if (slideInterval) {
                clearInterval(slideInterval);
            }

            slideInterval = setInterval(() => {
                const nextIndex = (currentIndex + 1) % galleryThumbs.length;
                const nextThumb = galleryThumbs[nextIndex];
                if (!nextThumb) {
                    return;
                }

                setMainImage(nextThumb.dataset.image, nextIndex);
            }, 3000);
        };

        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const nextImage = thumb.dataset.image;
                const nextIndex = Number(thumb.dataset.index || 0);
                setMainImage(nextImage, nextIndex);
                startAutoSlide();
            });
        });

        startAutoSlide();
    </script>
@endsection
