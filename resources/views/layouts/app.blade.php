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
</head>
<body class="bg-slate-50 text-slate-800">
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
                            <a href="{{ route('cart.index') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Keranjang</a>
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
    </div>
</body>
</html>
