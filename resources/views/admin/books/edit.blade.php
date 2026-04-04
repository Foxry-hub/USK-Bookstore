@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="text-2xl font-bold text-slate-900">Edit Buku</h1>

        <form action="{{ route('admin.books.update', $book) }}" method="POST" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            @method('PUT')
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Judul</label>
                <input type="text" name="title" value="{{ old('title', $book->title) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('title') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Penulis</label>
                <input type="text" name="author" value="{{ old('author', $book->author) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('author') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kategori</label>
                <select name="category_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $book->category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Harga</label>
                <input type="number" name="price" value="{{ old('price', $book->price) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" required>
                @error('price') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">URL Gambar Utama</label>
                <input type="url" name="image_url" value="{{ old('image_url', $book->image_url) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
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
                        $oldImages = old('image_urls', $book->image_urls ?? ['']);
                    @endphp
                    @foreach ($oldImages as $index => $oldImage)
                        <div class="flex gap-2">
                            <input type="url" name="image_urls[]" value="{{ $oldImage }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/extra-image-{{ $index + 1 }}.jpg">
                            <button type="button" class="remove-image-field rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-600">Hapus</button>
                        </div>
                    @endforeach
                </div>
                @error('image_urls') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('image_urls.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700">Preview Gambar</label>
                    <span class="text-xs text-slate-500">Preview akan berubah otomatis saat URL diubah</span>
                </div>
                <div id="image-previews" class="grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
                <p class="mt-2 text-xs text-slate-500">Gunakan URL langsung ke file gambar (contoh berakhiran .jpg, .png, .webp).</p>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Deskripsi Singkat</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">{{ old('description', $book->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Detail Lengkap</label>
                <textarea name="detail" rows="5" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="Cerita buku, ulasan, atau informasi detail lainnya...">{{ old('detail', $book->detail) }}</textarea>
                @error('detail') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white">Update Buku</button>
                <a href="{{ route('admin.books.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>

    <script>
        const imageFieldsContainer = document.getElementById('image-fields');
        const addImageFieldButton = document.getElementById('add-image-field');
        const imagePreviewContainer = document.getElementById('image-previews');
        const mainImageInput = document.querySelector('input[name="image_url"]');

        function getImageUrls() {
            const urls = [];

            if (mainImageInput?.value?.trim()) {
                urls.push(mainImageInput.value.trim());
            }

            imageFieldsContainer.querySelectorAll('input[name="image_urls[]"]').forEach(input => {
                if (input.value.trim()) {
                    urls.push(input.value.trim());
                }
            });

            return [...new Set(urls)];
        }

        function renderImagePreview() {
            const urls = getImageUrls();
            imagePreviewContainer.innerHTML = '';

            if (urls.length === 0) {
                imagePreviewContainer.innerHTML = '<div class="col-span-full rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">Belum ada URL gambar untuk dipreview.</div>';
                return;
            }

            urls.forEach((url, index) => {
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-xl border border-slate-200 bg-white';
                card.innerHTML = `
                    <div class="h-24 w-full bg-slate-100">
                        <img src="${url}" alt="Preview gambar ${index + 1}" class="h-full w-full object-cover" onerror="this.closest('div').classList.add('flex','items-center','justify-center'); this.replaceWith(Object.assign(document.createElement('span'), {className:'text-xs text-rose-600', textContent:'Gagal memuat gambar'}));">
                    </div>
                    <p class="truncate px-2 py-1 text-[11px] text-slate-600" title="${url}">${url}</p>
                `;
                imagePreviewContainer.appendChild(card);
            });
        }

        function bindImageInputListeners() {
            imageFieldsContainer.querySelectorAll('input[name="image_urls[]"]').forEach(input => {
                input.addEventListener('input', renderImagePreview);
                input.addEventListener('change', renderImagePreview);
            });
        }

        function bindRemoveButtons() {
            imageFieldsContainer.querySelectorAll('.remove-image-field').forEach(button => {
                button.onclick = () => {
                    const rows = imageFieldsContainer.querySelectorAll('.flex');
                    if (rows.length <= 1) {
                        const input = rows[0].querySelector('input[name="image_urls[]"]');
                        if (input) {
                            input.value = '';
                        }
                        renderImagePreview();
                        return;
                    }

                    button.closest('.flex')?.remove();
                    renderImagePreview();
                };
            });
        }

        addImageFieldButton.addEventListener('click', () => {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex gap-2';
            wrapper.innerHTML = `
                <input type="url" name="image_urls[]" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/extra-image.jpg">
                <button type="button" class="remove-image-field rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-600">Hapus</button>
            `;
            imageFieldsContainer.appendChild(wrapper);
            bindRemoveButtons();
            bindImageInputListeners();
            renderImagePreview();
        });

        mainImageInput?.addEventListener('input', renderImagePreview);
        mainImageInput?.addEventListener('change', renderImagePreview);
        bindImageInputListeners();
        bindRemoveButtons();
        renderImagePreview();
    </script>
@endsection
