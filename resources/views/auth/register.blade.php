@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Daftar Akun Baru</h1>
        <p class="mt-2 text-sm text-slate-600">Bikin akun dulu, habis itu kamu bisa langsung checkout COD.</p>

        <form action="{{ route('register.store') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                <input type="password" name="password" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-brand-500 focus:outline-none" required>
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">
                Buat Akun
            </button>
        </form>
    </section>
@endsection
