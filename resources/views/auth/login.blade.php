@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Login Akun</h1>
        <p class="mt-2 text-sm text-slate-600">Masuk dulu ya biar bisa belanja dan checkout COD.</p>

        <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-4">
            @csrf
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
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> Ingat saya
            </label>

            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">
                Login Sekarang
            </button>
        </form>
    </section>
@endsection
