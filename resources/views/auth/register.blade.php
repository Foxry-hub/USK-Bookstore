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
                <div class="relative">
                    <input type="password" name="password" data-password-input class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 text-sm focus:border-brand-500 focus:outline-none" required>
                    <button type="button" data-password-toggle class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-slate-500 transition hover:text-slate-700" aria-label="Tampilkan password">
                        <svg data-eye-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58A2 2 0 0 0 13.42 13.42" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A9.76 9.76 0 0 1 12 5c4.48 0 8.27 2.94 9.54 7a9.77 9.77 0 0 1-4.13 5.14" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.23 6.23A9.77 9.77 0 0 0 2.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.14-.38 4.5-1.06" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" data-password-input class="w-full rounded-xl border border-slate-300 px-4 py-3 pr-12 text-sm focus:border-brand-500 focus:outline-none" required>
                    <button type="button" data-password-toggle class="absolute inset-y-0 right-0 inline-flex w-12 items-center justify-center text-slate-500 transition hover:text-slate-700" aria-label="Tampilkan password">
                        <svg data-eye-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58A2 2 0 0 0 13.42 13.42" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.09A9.76 9.76 0 0 1 12 5c4.48 0 8.27 2.94 9.54 7a9.77 9.77 0 0 1-4.13 5.14" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.23 6.23A9.77 9.77 0 0 0 2.46 12c1.27 4.06 5.06 7 9.54 7 1.61 0 3.14-.38 4.5-1.06" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">
                Buat Akun
            </button>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const wrapper = button.closest('.relative');
                    const input = wrapper ? wrapper.querySelector('[data-password-input]') : null;

                    if (!input) {
                        return;
                    }

                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';

                    const eyeOpen = button.querySelector('[data-eye-open]');
                    const eyeClosed = button.querySelector('[data-eye-closed]');

                    eyeOpen?.classList.toggle('hidden', isPassword);
                    eyeClosed?.classList.toggle('hidden', !isPassword);
                    button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
                });
            });
        });
    </script>
@endsection
