@extends('layouts.app')

@section('content')
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900">CRUD Kategori Buku</h1>
            <a href="{{ route('admin.categories.create') }}" class="rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white">Tambah Kategori</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Slug</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->slug }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $categories->links() }}</div>
    </section>
@endsection
