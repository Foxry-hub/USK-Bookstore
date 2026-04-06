@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        <div>
            <a href="{{ route('admin.messages.index') }}" class="text-sm font-semibold text-brand-600">&larr; Kembali ke daftar pesan</a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Detail Pesan</h1>
            <p class="mt-1 text-sm text-slate-600">Halaman ini hanya untuk membaca isi pesan customer.</p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[1fr_1.6fr]">
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $message->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                        <p class="mt-1 text-base font-semibold text-slate-900">{{ $message->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                        <p class="mt-1">
                            @if ($message->is_read)
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Sudah dibaca</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Belum dibaca</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diterima</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $message->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    @if ($message->read_at)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dibaca</p>
                            <p class="mt-1 text-sm text-slate-700">{{ $message->read_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Isi Pesan</p>
                    <div class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-800">{{ $message->message }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection