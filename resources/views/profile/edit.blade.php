@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Profile User</h1>
                <p class="mt-1 text-sm text-slate-600">Lengkapi data ini supaya checkout langsung terisi otomatis.</p>
            </div>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Lihat Paket</a>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <input type="email" value="{{ $user->email }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500" readonly>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">No. Telp</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
                @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Alamat</label>
                <textarea name="address" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>{{ old('address', $user->address) }}</textarea>
                @error('address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">Simpan Profile</button>
        </form>
    </section>
@endsection