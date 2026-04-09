<aside class="hidden w-64 shrink-0 lg:block">
    <div class="sticky top-24 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin Menu</p>
        <div class="mt-4 space-y-2">
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
    </div>
</aside>

<div class="mb-6 lg:hidden">
    <div class="flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
        <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Dashboard</a>
        <a href="{{ route('admin.categories.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Kategori</a>
        <a href="{{ route('admin.books.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.books.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Buku</a>
        <a href="{{ route('admin.messages.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.messages.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">
            Pesan{{ $unreadContactMessages > 0 ? ' (' . $unreadContactMessages . ')' : '' }}
        </a>
        <a href="{{ route('admin.users.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">User</a>
        <a href="{{ route('admin.orders.index') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.orders.*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Pesanan</a>
        <a href="{{ route('admin.reports.financial') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.reports.financial*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Laporan</a>
        <a href="{{ route('admin.landing-preview') }}" class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold {{ request()->routeIs('admin.landing-preview') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">Preview Landing</a>
    </div>
</div>
