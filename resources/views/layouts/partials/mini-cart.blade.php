<div id="mini-cart-overlay" class="pointer-events-none fixed inset-0 z-40 bg-slate-950/30 opacity-0 backdrop-blur-[2px] transition-all duration-500 ease-out"></div>
<aside id="mini-cart-panel" class="fixed bottom-4 right-4 z-50 w-[calc(100vw-2rem)] max-w-sm translate-y-4 scale-[0.98] opacity-0 pointer-events-none transition-all duration-500 ease-out will-change-transform sm:bottom-6 sm:right-6 sm:w-96">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-black/5">
        <div class="border-b border-slate-100 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Keranjang Kamu</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Daftar buku di keranjang</h2>
        </div>

        <div class="max-h-[24rem] overflow-y-auto px-5 py-4">
            <div id="mini-cart-empty" class="{{ $miniCartItems->isEmpty() ? '' : 'hidden' }} rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                Keranjang masih kosong. Tambahkan buku untuk melihat daftarnya di sini.
            </div>

            <div id="mini-cart-items" class="space-y-3 {{ $miniCartItems->isEmpty() ? 'hidden' : '' }}">
                @foreach ($miniCartItems as $item)
                    <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-transparent transition-all duration-200 hover:ring-brand-300" data-cart-mini-item="{{ $item['book_id'] }}">
                        <div class="flex gap-3">
                            <img src="{{ $item['image_url'] ?: 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $item['title'] }}" class="h-16 w-12 rounded-lg object-cover">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                                    <span class="inline-flex shrink-0 items-center rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-600">x{{ $item['quantity'] }}</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1">
                                        <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-slate-700 transition hover:bg-slate-100" data-cart-action="decrease" data-book-id="{{ $item['book_id'] }}">-</button>
                                        <span class="min-w-10 px-3 text-center text-sm font-semibold text-slate-900" data-cart-quantity="{{ $item['book_id'] }}">{{ $item['quantity'] }}</span>
                                        <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-slate-700 transition hover:bg-slate-100" data-cart-action="increase" data-book-id="{{ $item['book_id'] }}">+</button>
                                    </div>

                                    <button type="button" class="inline-flex items-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50" data-cart-action="remove" data-book-id="{{ $item['book_id'] }}">x</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Total</span>
                <span id="mini-cart-total" class="text-base font-bold text-slate-900">Rp {{ number_format($miniCartTotal, 0, ',', '.') }}</span>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('cart.index') }}" class="rounded-xl bg-brand-500 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-brand-600">Buka Keranjang</a>
                <a href="{{ route('store.catalog') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Lanjut Belanja</a>
            </div>
        </div>
    </div>
</aside>
