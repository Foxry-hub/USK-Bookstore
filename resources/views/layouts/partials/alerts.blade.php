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
