@extends('layouts.app')

@section('content')
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-900">CRUD Data Buku</h1>
            <a href="{{ route('admin.books.create') }}" class="rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white">Tambah Buku</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-2.5">Judul</th>
                        <th class="px-4 py-2.5">ISBN</th>
                        <th class="px-4 py-2.5">Penulis</th>
                        <th class="px-4 py-2.5">Kategori</th>
                        <th class="px-4 py-2.5">Harga</th>
                        <th class="px-4 py-2.5">Stock</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-2.5 font-semibold text-slate-900">{{ $book->title }}</td>
                            <td class="px-4 py-2.5">{{ $book->isbn }}</td>
                            <td class="px-4 py-2.5">{{ $book->author }}</td>
                            <td class="px-4 py-2.5">{{ $book->category->name }}</td>
                            <td class="px-4 py-2.5">Rp {{ number_format($book->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5">
                                @if ((int) $book->stock <= 0)
                                    <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-700">Habis (0)</span>
                                @elseif ((int) $book->stock <= 5)
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Menipis ({{ $book->stock }})</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">{{ $book->stock }} tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="rounded-lg border border-slate-300 px-2.5 py-1 text-[11px] font-semibold text-slate-700">Edit</a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 px-2.5 py-1 text-[11px] font-semibold text-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $books->links() }}</div>
    </section>
@endsection
