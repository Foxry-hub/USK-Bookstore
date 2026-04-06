@extends('layouts.app')

@section('content')
    <section>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pesan Masuk Customer</h1>
                <p class="mt-1 text-sm text-slate-600">Semua pesan dari form kontak landing page tampil di sini. Admin hanya membaca isi pesan.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Belum dibaca</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $messages->where('is_read', false)->count() }}</p>
            </div>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
            Pesan yang belum dibaca diberi latar lebih tegas supaya lebih mudah diprioritaskan.
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Pengirim</th>
                        <th class="px-4 py-3">Pesan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Diterima</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="border-t border-slate-100 align-top {{ $message->is_read ? 'bg-white' : 'bg-amber-50/50' }}">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $message->name }}</p>
                                <p class="text-slate-500">{{ $message->email }}</p>
                                @if ($message->user)
                                    <p class="mt-1 text-xs text-slate-400">User terdaftar: {{ $message->user->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700 {{ $message->is_read ? '' : 'font-medium text-slate-900' }}">
                                {{ \Illuminate\Support\Str::limit($message->message, 90) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($message->is_read)
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Sudah dibaca</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Belum dibaca</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $message->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.messages.show', $message) }}" class="inline-flex rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">Belum ada pesan masuk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $messages->links() }}</div>
    </section>
@endsection