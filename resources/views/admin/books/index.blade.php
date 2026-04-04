@extends('layouts.app')

@section('content')
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">CRUD Data Buku</h1>
            <a href="{{ route('admin.books.create') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Tambah Buku</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Judul</th>
                        <th class="px-4 py-3">Penulis</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $book->title }}</td>
                            <td class="px-4 py-3">{{ $book->author }}</td>
                            <td class="px-4 py-3">{{ $book->category->name }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($book->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Edit</a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $books->links() }}</div>
    </section>
@endsection
