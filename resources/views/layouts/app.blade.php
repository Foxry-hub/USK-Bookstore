<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'BookStore' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            500: '#2563eb',
                            600: '#1d4ed8',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    @php
        $miniCartItems = auth()->check() && !auth()->user()->isAdmin() ? collect(request()->session()->get('cart', []))->values() : collect();
        $miniCartTotal = $miniCartItems->sum(fn (array $item): float => $item['price'] * $item['quantity']);
        $miniCartCount = $miniCartItems->sum('quantity');
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,_rgba(37,99,235,0.10),_transparent_40%),radial-gradient(circle_at_bottom_left,_rgba(16,185,129,0.10),_transparent_45%)]">
        <nav class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('store.index') }}" class="text-lg font-extrabold tracking-tight text-slate-900">BookStore</a>
                <div class="flex items-center gap-2 sm:gap-4">
                    @if (!auth()->check())
                        <a href="{{ route('store.index') }}#about" class="hidden text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline">About Us</a>
                        <a href="{{ route('store.index') }}#contact" class="hidden text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline">Kontak</a>
                    @endif

                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Dashboard Admin</a>
                        @else
                            <a href="{{ route('cart.index') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Keranjang (<span id="mini-cart-count">{{ $miniCartCount }}</span>)</a>
                            <a href="{{ route('orders.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Pesanan</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="rounded-xl border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Login</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        @if (auth()->check() && auth()->user()->isAdmin() && request()->routeIs('admin.*'))
            <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:px-8">
                <aside class="hidden w-64 shrink-0 lg:block">
                    <div class="sticky top-24 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin Menu</p>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('admin.dashboard') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                            <a href="{{ route('admin.categories.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Kategori</a>
                            <a href="{{ route('admin.books.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.books.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Buku</a>
                            <a href="{{ route('admin.users.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">User</a>
                            <a href="{{ route('admin.orders.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Pesanan</a>
                        </div>
                    </div>
                </aside>

                <main class="min-w-0 flex-1">
                    <div class="mb-6 lg:hidden">
                        <div class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
                            <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Dashboard</a>
                            <a href="{{ route('admin.categories.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Kategori</a>
                            <a href="{{ route('admin.books.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.books.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Buku</a>
                            <a href="{{ route('admin.users.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">User</a>
                            <a href="{{ route('admin.orders.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Pesanan</a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        @else
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        @endif

        @auth
            @if (!auth()->user()->isAdmin())
                <div id="mini-cart-overlay" class="pointer-events-none fixed inset-0 z-40 bg-slate-950/30 opacity-0 transition-opacity duration-300"></div>
                <aside id="mini-cart-panel" class="fixed bottom-4 right-4 z-50 w-[calc(100vw-2rem)] max-w-sm translate-y-3 opacity-0 pointer-events-none transition-all duration-300 sm:bottom-6 sm:right-6 sm:w-96">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-black/5">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-500">Keranjang Kamu</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-900">Daftar buku di keranjang</h2>
                            </div>
                        </div>

                        <div class="max-h-[24rem] overflow-y-auto px-5 py-4">
                            <div id="mini-cart-empty" class="{{ $miniCartItems->isEmpty() ? '' : 'hidden' }} rounded-2xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                                Keranjang masih kosong. Tambahkan buku untuk melihat daftarnya di sini.
                            </div>

                            <div id="mini-cart-items" class="space-y-3 {{ $miniCartItems->isEmpty() ? 'hidden' : '' }}">
                                @foreach ($miniCartItems as $item)
                                    <div class="rounded-2xl bg-slate-50 p-3" data-cart-mini-item="{{ $item['book_id'] }}">
                                        <div class="flex gap-3">
                                            <img src="{{ $item['image_url'] ?: 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $item['title'] }}" class="h-16 w-12 rounded-lg object-cover">
                                            <div class="min-w-0 flex-1">
                                                <p class="line-clamp-2 text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
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
            @endif
        @endauth
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const cartPanel = document.getElementById('mini-cart-panel');
            const cartOverlay = document.getElementById('mini-cart-overlay');
            const cartItems = document.getElementById('mini-cart-items');
            const cartEmpty = document.getElementById('mini-cart-empty');
            const cartTotal = document.getElementById('mini-cart-total');
            const cartCount = document.getElementById('mini-cart-count');
            const cartPageItems = document.getElementById('cart-items');
            const cartPageEmptyTemplate = document.getElementById('cart-empty-template');
            const cartPageTotal = document.getElementById('cart-total');

            const openCart = () => {
                if (!cartPanel || !cartOverlay) {
                    return;
                }

                cartPanel.classList.remove('translate-y-3', 'opacity-0', 'pointer-events-none');
                cartPanel.classList.add('translate-y-0', 'opacity-100');
                cartOverlay.classList.remove('opacity-0', 'pointer-events-none');
                cartOverlay.classList.add('opacity-100');
            };

            const closeCart = () => {
                if (!cartPanel || !cartOverlay) {
                    return;
                }

                cartPanel.classList.add('translate-y-3', 'opacity-0', 'pointer-events-none');
                cartPanel.classList.remove('translate-y-0', 'opacity-100');
                cartOverlay.classList.add('opacity-0', 'pointer-events-none');
                cartOverlay.classList.remove('opacity-100');
            };

            const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(value);
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[character]);

            const buildMiniCartRow = (item) => `
                <div class="rounded-2xl bg-slate-50 p-3" data-cart-mini-item="${item.book_id}">
                    <div class="flex gap-3">
                        <img src="${escapeHtml(item.image_url || 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80')}" alt="${escapeHtml(item.title)}" class="h-16 w-12 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 text-sm font-semibold text-slate-900">${escapeHtml(item.title)}</p>
                            <p class="mt-1 text-xs text-slate-500">Rp ${formatCurrency(item.price)}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <div class="inline-flex items-center rounded-xl border border-slate-200 bg-white p-1">
                                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-slate-700 transition hover:bg-slate-100" data-cart-action="decrease" data-book-id="${item.book_id}">-</button>
                                    <span class="min-w-10 px-3 text-center text-sm font-semibold text-slate-900" data-cart-quantity="${item.book_id}">${item.quantity}</span>
                                    <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-bold text-slate-700 transition hover:bg-slate-100" data-cart-action="increase" data-book-id="${item.book_id}">+</button>
                                </div>

                                <button type="button" class="inline-flex items-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50" data-cart-action="remove" data-book-id="${item.book_id}">x</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const buildCartPageRow = (item) => `
                <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between" data-cart-item="${item.book_id}">
                    <div class="flex items-center gap-4">
                        <img src="${escapeHtml(item.image_url || 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80')}" alt="${escapeHtml(item.title)}" class="h-20 w-16 rounded-lg object-cover">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">${escapeHtml(item.title)}</h3>
                            <p class="text-sm text-slate-600">Rp ${formatCurrency(item.price)}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="decrease" data-book-id="${item.book_id}">-</button>
                            <span class="min-w-10 px-3 text-center text-sm font-semibold text-slate-900" data-cart-quantity="${item.book_id}">${item.quantity}</span>
                            <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="increase" data-book-id="${item.book_id}">+</button>
                        </div>

                        <form action="{{ url('/cart') }}/${item.book_id}" method="POST" class="cart-remove-form">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600">Hapus</button>
                        </form>
                    </div>
                </article>
            `;

            const renderMiniCartItems = (items) => {
                if (!cartItems || !cartEmpty) {
                    return;
                }

                if (!items || items.length === 0) {
                    cartItems.innerHTML = '';
                    cartItems.classList.add('hidden');
                    cartEmpty.classList.remove('hidden');
                    return;
                }

                cartEmpty.classList.add('hidden');
                cartItems.classList.remove('hidden');
                cartItems.innerHTML = items.map((item) => buildMiniCartRow(item)).join('');
            };

            const renderCartPageItems = (items) => {
                if (!cartPageItems || !cartPageEmptyTemplate) {
                    return;
                }

                if (!items || items.length === 0) {
                    cartPageItems.innerHTML = '';
                    cartPageItems.appendChild(cartPageEmptyTemplate.content.cloneNode(true));
                    return;
                }

                cartPageItems.innerHTML = items.map((item) => buildCartPageRow(item)).join('');
            };

            const syncState = (payload, options = {}) => {
                if (cartTotal && typeof payload.total !== 'undefined') {
                    cartTotal.textContent = `Rp ${formatCurrency(payload.total)}`;
                }

                if (cartPageTotal && typeof payload.total !== 'undefined') {
                    cartPageTotal.textContent = `Rp ${formatCurrency(payload.total)}`;
                }

                if (cartCount && typeof payload.cart_count !== 'undefined') {
                    cartCount.textContent = payload.cart_count;
                }

                if (typeof payload.items !== 'undefined') {
                    renderMiniCartItems(payload.items);
                    renderCartPageItems(payload.items);
                }

                if (options.openPanel) {
                    openCart();
                }
            };
            cartOverlay?.addEventListener('click', closeCart);

            document.addEventListener('click', async (event) => {
                const actionButton = event.target.closest('[data-cart-action]');

                if (!actionButton) {
                    return;
                }

                const action = actionButton.dataset.cartAction;
                const bookId = actionButton.dataset.bookId;
                const quantityBadge = document.querySelector(`[data-cart-quantity="${bookId}"]`);
                const currentQuantity = Number(quantityBadge?.textContent || 1);

                if (action === 'remove') {
                    const response = await fetch(`{{ url('/cart') }}/${bookId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    syncState(payload);
                    return;
                }

                const nextQuantity = action === 'decrease' ? currentQuantity - 1 : currentQuantity + 1;

                const response = await fetch(`{{ url('/cart') }}/${bookId}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ quantity: nextQuantity }),
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                syncState(payload);
            });

            document.querySelectorAll('[data-cart-add]').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: new FormData(form),
                    });

                    if (!response.ok) {
                        form.submit();
                        return;
                    }

                    const payload = await response.json();
                    syncState(payload, { openPanel: true });
                });
            });

            document.addEventListener('submit', async (event) => {
                const form = event.target;

                if (!form.matches('.cart-remove-form')) {
                    return;
                }

                event.preventDefault();

                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams(formData),
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                syncState(payload);
            });
        });
    </script>
</body>
</html>
