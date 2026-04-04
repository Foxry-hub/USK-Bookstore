@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Kategori</h1>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Kategori</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white">Update</button>
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>
@endsection
