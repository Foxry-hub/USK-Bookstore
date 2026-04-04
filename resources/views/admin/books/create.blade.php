@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Buku</h1>

        <form action="{{ route('admin.books.store') }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Judul</label>
                <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Penulis</label>
                <input type="text" name="author" value="{{ old('author') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('author') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kategori</label>
                <select name="category_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Harga</label>
                <input type="number" name="price" value="{{ old('price') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">URL Gambar Utama</label>
                <input type="url" name="image_url" value="{{ old('image_url') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/...">
                @error('image_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label class="block text-sm font-semibold text-slate-700">Gambar Tambahan</label>
                    <button type="button" id="add-image-field" aria-label="Tambah kolom gambar" title="Tambah kolom gambar" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-900 bg-slate-900 text-white transition hover:bg-slate-800">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                    </button>
                </div>
                <div id="image-fields" class="space-y-2">
                    @php
                        $oldImages = old('image_urls', ['']);
                    @endphp
                    @foreach ($oldImages as $index => $oldImage)
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="url" name="image_urls[]" value="{{ $oldImage }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/extra-image-{{ $index + 1 }}.jpg">
                            <button type="button" class="remove-image-field w-full rounded-xl border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-600 sm:w-auto">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('image_urls') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('image_urls.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Deskripsi Singkat</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Detail Lengkap</label>
                <textarea name="detail" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="Cerita buku, ulasan, atau informasi detail lainnya...">{{ old('detail') }}</textarea>
                @error('detail') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white sm:w-auto">Simpan Buku</button>
                <a href="{{ route('admin.books.index') }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 sm:w-auto">Batal</a>
            </div>
        </form>
    </section>

    <script>
        const imageFieldsContainer = document.getElementById('image-fields');
        const addImageFieldButton = document.getElementById('add-image-field');

        function bindRemoveButtons() {
            imageFieldsContainer.querySelectorAll('.remove-image-field').forEach(button => {
                button.onclick = () => {
                    const rows = imageFieldsContainer.querySelectorAll('.flex');
                    if (rows.length <= 1) {
                        const input = rows[0].querySelector('input[name="image_urls[]"]');
                        if (input) {
                            input.value = '';
                        }
                        return;
                    }

                    button.closest('.flex')?.remove();
                };
            });
        }

        addImageFieldButton.addEventListener('click', () => {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex flex-col gap-2 sm:flex-row';
            wrapper.innerHTML = `
                <input type="url" name="image_urls[]" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/extra-image.jpg">
                <button type="button" class="remove-image-field w-full rounded-xl border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-600 sm:w-auto">Hapus</button>
            `;
            imageFieldsContainer.appendChild(wrapper);
            bindRemoveButtons();
        });

        bindRemoveButtons();
    </script>
@endsection
