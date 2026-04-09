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

        @keyframes cart-pop {
            0% {
                transform: scale(1);
            }
            45% {
                transform: scale(1.12);
            }
            100% {
                transform: scale(1);
            }
        }

        .cart-pop {
            animation: cart-pop 220ms ease-out;
        }

        .cart-item-flash {
            animation: cart-pop 260ms ease-out;
        }

        @keyframes cart-fade-out {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(10px);
            }
        }

        .cart-removing {
            animation: cart-fade-out 220ms ease-out forwards;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">
    @php
        $isAdminUser = auth()->check() && auth()->user()->isAdmin();
        $isAdminRoute = request()->routeIs('admin.*');
        $isAdminLandingPreview = $isAdminUser && request()->routeIs('admin.landing-preview');
        $unreadContactMessages = $isAdminUser ? \App\Models\ContactMessage::where('is_read', false)->count() : 0;
        $miniCartItems = auth()->check() && !auth()->user()->isAdmin() ? collect(request()->session()->get('cart', []))->values() : collect();
        $miniCartTotal = $miniCartItems->sum(fn (array $item): float => $item['price'] * $item['quantity']);
        $miniCartCount = $miniCartItems->sum('quantity');
        $dueCodNotifications = auth()->check() && !auth()->user()->isAdmin()
            ? \App\Models\Order::query()
                ->where('user_id', auth()->id())
                ->where('payment_method', 'COD')
                ->where('status', 'Dikirim')
                ->whereNotNull('estimated_delivery_at')
                ->where('estimated_delivery_at', '<=', now())
                ->whereNull('received_at')
                ->latest('estimated_delivery_at')
                ->limit(5)
                ->get()
            : collect();
        $dueCodNotificationCount = $dueCodNotifications->count();
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,_rgba(37,99,235,0.10),_transparent_40%),radial-gradient(circle_at_bottom_left,_rgba(16,185,129,0.10),_transparent_45%)]">
        <nav class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    @if ($isAdminLandingPreview)
                        <button type="button" id="admin-preview-sidebar-open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-300 text-slate-700 transition hover:bg-slate-100" aria-label="Buka sidebar admin" title="Buka sidebar admin">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    @endif

                    @if ($isAdminUser)
                        <span class="inline-flex select-none items-center gap-2.5 text-lg font-extrabold tracking-tight text-slate-900">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white">
                                <img src="{{ asset('assets/logo.png') }}" alt="Logo BookStore" class="h-5 w-5 object-contain">
                            </span>
                            <span>BookStore</span>
                        </span>
                    @else
                        <a href="{{ route('store.index') }}" class="inline-flex items-center gap-2.5 text-lg font-extrabold tracking-tight text-slate-900">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-300 bg-white">
                                <img src="{{ asset('assets/logo.png') }}" alt="Logo BookStore" class="h-5 w-5 object-contain">
                            </span>
                            <span>BookStore</span>
                        </a>
                    @endif
                </div>

                @if ($isAdminLandingPreview)
                    <div class="hidden items-center gap-7 text-sm font-medium text-slate-600 lg:flex">
                        <span class="cursor-not-allowed text-slate-400">Home</span>
                        <span class="cursor-not-allowed text-slate-400">Product</span>
                        <span class="cursor-not-allowed text-slate-400">About Us</span>
                        <span class="cursor-not-allowed text-slate-400">Contact</span>
                    </div>
                @elseif (!$isAdminUser)
                    <div class="hidden items-center gap-7 text-sm font-medium text-slate-600 lg:flex">
                        <a href="{{ route('store.index') }}" class="transition hover:text-slate-900">Home</a>
                        <a href="{{ route('store.index') }}#catalog" class="transition hover:text-slate-900">Product</a>
                        <a href="{{ route('store.index') }}#about" class="transition hover:text-slate-900">About Us</a>
                        <a href="{{ route('store.index') }}#contact" class="transition hover:text-slate-900">Contact</a>
                    </div>
                @endif

                <div class="flex items-center gap-2 sm:gap-4">
                    @auth
                        @if ($isAdminUser)
                            <a href="{{ route('admin.dashboard') }}" class="relative rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                                Dashboard Admin
                                @if ($unreadContactMessages > 0)
                                    <span class="absolute -right-2 -top-2 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border border-white bg-rose-500 px-1 text-[11px] font-bold text-white">{{ $unreadContactMessages }}</span>
                                @endif
                            </a>
                        @else
                            <a
                                href="{{ route('cart.index') }}"
                                class="relative inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm ring-1 ring-brand-600/20 transition hover:bg-brand-600 hover:shadow-md"
                                aria-label="Buka keranjang"
                                title="Keranjang"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-6 w-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h1.22c.58 0 1.08.4 1.22.96l1.42 5.84c.2.8.92 1.4 1.75 1.4h7.33c.8 0 1.5-.53 1.71-1.31l1.33-4.89H7.16" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 19.5a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0ZM17.1 19.5a.9.9 0 1 1-1.8 0 .9.9 0 0 1 1.8 0" />
                                </svg>
                                <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border border-white bg-slate-900 px-1 text-[11px] font-bold text-white" id="mini-cart-count">{{ $miniCartCount }}</span>
                            </a>
                            <a
                                href="{{ route('orders.index') }}"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-300 text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900"
                                title="Paket"
                                aria-label="Paket"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511v7.24a2.25 2.25 0 0 1-1.12 1.95l-6.75 3.857a2.25 2.25 0 0 1-2.26 0l-6.75-3.857a2.25 2.25 0 0 1-1.12-1.95v-7.24m18 0a2.25 2.25 0 0 0-1.133-1.957l-6.75-3.857a2.25 2.25 0 0 0-2.234 0L3.383 6.554A2.25 2.25 0 0 0 2.25 8.511m18 0-8.25 4.714m-9.75-4.714 8.25 4.714m0 0v8.775" />
                                </svg>
                            </a>
                            <a
                                href="{{ route('profile.edit') }}"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-300 text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900"
                                title="Profile"
                                aria-label="Profile"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75a17.933 17.933 0 0 1-7.499-1.632Z" />
                                </svg>
                            </a>
                            <div class="relative">
                                <button
                                    type="button"
                                    id="notification-bell-button"
                                    class="relative inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-300 text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-900"
                                    aria-label="Notifikasi COD"
                                    title="Notifikasi COD"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 17.5h-4.5m8.25-2.75H6a1.5 1.5 0 0 1-.75-2.799l.558-.32A1.5 1.5 0 0 0 6.5 10.33V9a5.5 5.5 0 1 1 11 0v1.33c0 .538.287 1.036.75 1.3l.558.32A1.5 1.5 0 0 1 18 14.75Z" />
                                    </svg>
                                    @if ($dueCodNotificationCount > 0)
                                        <span class="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full border border-white bg-rose-500 px-1 text-[11px] font-bold text-white">{{ $dueCodNotificationCount }}</span>
                                    @endif
                                </button>

                                <div id="notification-bell-panel" class="pointer-events-none absolute right-0 z-50 mt-2 hidden w-80 rounded-2xl border border-slate-200 bg-white p-3 opacity-0 shadow-xl transition">
                                    <p class="px-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Notifikasi COD</p>
                                    @if ($dueCodNotificationCount === 0)
                                        <p class="mt-2 rounded-xl bg-slate-50 px-3 py-4 text-sm text-slate-600">Belum ada notifikasi paket COD.</p>
                                    @else
                                        <div class="mt-2 space-y-2">
                                            @foreach ($dueCodNotifications as $notificationOrder)
                                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                                    <p class="text-sm font-semibold text-slate-900">{{ $notificationOrder->order_code }}</p>
                                                    <p class="mt-1 text-xs text-slate-600">Paket COD sudah sampai estimasi. Konfirmasi diterima dan dibayar.</p>
                                                    <a href="{{ route('orders.index', ['status' => 'dikirim', 'open_order' => $notificationOrder->id, 'focus_order' => $notificationOrder->id]) }}" class="mt-2 inline-flex rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                        Buka Paket Ini
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-rose-200 text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700"
                                aria-label="Logout"
                                title="Logout"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 4.5h3.75A1.5 1.5 0 0 1 15.75 6v12a1.5 1.5 0 0 1-1.5 1.5H10.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 12h-8.5m0 0 2.25-2.25m-2.25 2.25 2.25 2.25" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Login</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        @if ($isAdminLandingPreview)
            <div id="admin-preview-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-950/30 opacity-0 transition"></div>
            <aside id="admin-preview-sidebar" class="fixed left-0 top-0 z-50 h-full w-72 -translate-x-full overflow-y-auto border-r border-slate-200 bg-white p-4 shadow-2xl transition-transform duration-300">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin Menu</p>
                    <button type="button" id="admin-preview-sidebar-close" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100" aria-label="Tutup sidebar admin" title="Tutup sidebar admin">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
                    <a href="{{ route('admin.categories.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Kategori</a>
                    <a href="{{ route('admin.books.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.books.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Buku</a>
                    <a href="{{ route('admin.messages.index') }}" class="flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.messages.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                        <span>Pesan Masuk</span>
                        @if ($unreadContactMessages > 0)
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">{{ $unreadContactMessages }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">User</a>
                    <a href="{{ route('admin.orders.index') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Pesanan</a>
                    <a href="{{ route('admin.reports.financial') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.reports.financial*') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Laporan</a>
                    <a href="{{ route('admin.landing-preview') }}" class="block rounded-2xl px-4 py-3 text-sm font-semibold {{ request()->routeIs('admin.landing-preview') ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">Preview Landing</a>
                </div>
            </aside>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @include('layouts.partials.alerts')
                <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                    Mode Preview Admin aktif. Tampilan landing page hanya untuk dilihat dan tidak bisa diinteraksikan.
                </div>
                <div class="admin-preview-locked">
                    @yield('content')
                </div>
            </main>
        @elseif (auth()->check() && auth()->user()->isAdmin() && request()->routeIs('admin.*'))
            <div class="mx-auto flex max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:px-8">
                @include('layouts.partials.admin-navigation')

                <main class="min-w-0 flex-1">
                    @include('layouts.partials.alerts')
                    @yield('content')
                </main>
            </div>
        @else
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @include('layouts.partials.alerts')
                @yield('content')
            </main>
        @endif

        @auth
            @if (!auth()->user()->isAdmin())
                @include('layouts.partials.mini-cart')
            @endif
        @endauth
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const notificationBellButton = document.getElementById('notification-bell-button');
            const notificationBellPanel = document.getElementById('notification-bell-panel');
            const cartPanel = document.getElementById('mini-cart-panel');
            const cartOverlay = document.getElementById('mini-cart-overlay');
            const cartItems = document.getElementById('mini-cart-items');
            const cartEmpty = document.getElementById('mini-cart-empty');
            const cartTotal = document.getElementById('mini-cart-total');
            const cartCount = document.getElementById('mini-cart-count');
            const cartPageItems = document.getElementById('cart-items');
            const cartPageEmptyTemplate = document.getElementById('cart-empty-template');
            const cartPageTotal = document.getElementById('cart-total');
            const adminPreviewSidebar = document.getElementById('admin-preview-sidebar');
            const adminPreviewSidebarOverlay = document.getElementById('admin-preview-sidebar-overlay');
            const adminPreviewSidebarOpen = document.getElementById('admin-preview-sidebar-open');
            const adminPreviewSidebarClose = document.getElementById('admin-preview-sidebar-close');

            const openAdminPreviewSidebar = () => {
                if (!adminPreviewSidebar || !adminPreviewSidebarOverlay) {
                    return;
                }

                adminPreviewSidebar.classList.remove('-translate-x-full');
                adminPreviewSidebar.classList.add('translate-x-0');
                adminPreviewSidebarOverlay.classList.remove('hidden', 'opacity-0');
                adminPreviewSidebarOverlay.classList.add('opacity-100');
            };

            const closeAdminPreviewSidebar = () => {
                if (!adminPreviewSidebar || !adminPreviewSidebarOverlay) {
                    return;
                }

                adminPreviewSidebar.classList.add('-translate-x-full');
                adminPreviewSidebar.classList.remove('translate-x-0');
                adminPreviewSidebarOverlay.classList.add('opacity-0');
                adminPreviewSidebarOverlay.classList.remove('opacity-100');

                setTimeout(() => {
                    if (adminPreviewSidebarOverlay.classList.contains('opacity-0')) {
                        adminPreviewSidebarOverlay.classList.add('hidden');
                    }
                }, 200);
            };

            adminPreviewSidebarOpen?.addEventListener('click', openAdminPreviewSidebar);
            adminPreviewSidebarClose?.addEventListener('click', closeAdminPreviewSidebar);
            adminPreviewSidebarOverlay?.addEventListener('click', closeAdminPreviewSidebar);

            const openCart = () => {
                if (!cartPanel || !cartOverlay) {
                    return;
                }

                cartPanel.classList.remove('translate-y-4', 'scale-[0.98]', 'opacity-0', 'pointer-events-none');
                cartPanel.classList.add('translate-y-0', 'scale-100', 'opacity-100');
                cartOverlay.classList.remove('opacity-0', 'pointer-events-none');
                cartOverlay.classList.add('opacity-100');
            };

            const closeCart = () => {
                if (!cartPanel || !cartOverlay) {
                    return;
                }

                cartPanel.classList.add('translate-y-4', 'scale-[0.98]', 'opacity-0', 'pointer-events-none');
                cartPanel.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
                cartOverlay.classList.add('opacity-0', 'pointer-events-none');
                cartOverlay.classList.remove('opacity-100');
            };

            const openNotifications = () => {
                if (!notificationBellPanel) {
                    return;
                }

                notificationBellPanel.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
                notificationBellPanel.classList.add('opacity-100');
            };

            const closeNotifications = () => {
                if (!notificationBellPanel) {
                    return;
                }

                notificationBellPanel.classList.add('hidden', 'opacity-0', 'pointer-events-none');
                notificationBellPanel.classList.remove('opacity-100');
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
                <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-transparent transition-all duration-200 hover:ring-brand-300" data-cart-mini-item="${item.book_id}">
                    <div class="flex gap-3">
                        <img src="${escapeHtml(item.image_url || 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80')}" alt="${escapeHtml(item.title)}" class="h-16 w-12 rounded-lg object-cover">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="line-clamp-2 text-sm font-semibold text-slate-900">${escapeHtml(item.title)}</p>
                                <span class="inline-flex shrink-0 items-center rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-600">x${item.quantity}</span>
                            </div>
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
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" data-cart-item="${item.book_id}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <img src="${escapeHtml(item.image_url || 'https://images.unsplash.com/photo-1512820790803-d550eacf6090?auto=format&fit=crop&w=500&q=80')}" alt="${escapeHtml(item.title)}" class="h-20 w-14 rounded-lg object-cover">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">${escapeHtml(item.title)}</h3>
                                <p class="mt-1 text-xs text-slate-500">Rp ${formatCurrency(item.price)}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-700">Subtotal: Rp ${formatCurrency(item.price * item.quantity)}</p>
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

            const highlightItem = (selector) => {
                const target = document.querySelector(selector);

                if (!target) {
                    return;
                }

                target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
            };

            const setBookActionState = (bookId, isBusy) => {
                document.querySelectorAll(`[data-book-id="${bookId}"]`).forEach((button) => {
                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    button.disabled = isBusy;
                    button.classList.toggle('opacity-60', isBusy);
                    button.classList.toggle('cursor-not-allowed', isBusy);
                });
            };

            const animateRemovingItem = (bookId) => {
                document.querySelectorAll(`[data-cart-mini-item="${bookId}"], [data-cart-item="${bookId}"]`).forEach((element) => {
                    element.classList.add('cart-removing');
                });
            };

            const clearRemovingItem = (bookId) => {
                document.querySelectorAll(`[data-cart-mini-item="${bookId}"], [data-cart-item="${bookId}"]`).forEach((element) => {
                    element.classList.remove('cart-removing');
                });
            };

            const popElement = (element, className = 'cart-pop') => {
                if (!element) {
                    return;
                }

                element.classList.remove(className);
                void element.offsetWidth;
                element.classList.add(className);

                window.clearTimeout(element.__cartPopTimer);
                element.__cartPopTimer = window.setTimeout(() => {
                    element.classList.remove(className);
                }, 280);
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
                    popElement(cartCount);
                }

                if (typeof payload.items !== 'undefined') {
                    renderMiniCartItems(payload.items);
                    renderCartPageItems(payload.items);
                }

                if (payload.book_id && (options.openPanel || payload.removed === false || typeof options.focusItem !== 'undefined')) {
                    const selector = `[data-cart-mini-item="${payload.book_id}"]`;
                    highlightItem(selector);
                }

                if (payload.book_id) {
                    popElement(document.querySelector(`[data-cart-quantity="${payload.book_id}"]`), 'cart-item-flash');
                }

                if (options.openPanel) {
                    openCart();
                }
            };
            cartOverlay?.addEventListener('click', closeCart);

            if (notificationBellButton && notificationBellPanel) {
                notificationBellButton.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (notificationBellPanel.classList.contains('hidden')) {
                        openNotifications();
                        return;
                    }

                    closeNotifications();
                });

                document.addEventListener('click', (event) => {
                    const target = event.target;

                    if (!(target instanceof Node)) {
                        return;
                    }

                    if (!notificationBellPanel.contains(target) && !notificationBellButton.contains(target)) {
                        closeNotifications();
                    }
                });
            }

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
                    setBookActionState(bookId, true);
                    animateRemovingItem(bookId);

                    const response = await fetch(`{{ url('/cart') }}/${bookId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        clearRemovingItem(bookId);
                        setBookActionState(bookId, false);
                        return;
                    }

                    const payload = await response.json();
                    syncState(payload);
                    return;
                }

                const nextQuantity = action === 'decrease' ? currentQuantity - 1 : currentQuantity + 1;

                setBookActionState(bookId, true);

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
                    setBookActionState(bookId, false);
                    return;
                }

                const payload = await response.json();
                syncState(payload);
                setBookActionState(bookId, false);
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
                    syncState(payload, { openPanel: true, focusItem: true });
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

    @if ($isAdminLandingPreview)
        <style>
            .admin-preview-locked {
                position: relative;
            }

            .admin-preview-locked::after {
                content: '';
                position: absolute;
                inset: 0;
                z-index: 30;
                background: transparent;
            }
        </style>
    @endif
</body>
</html>
