@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-2xl border border-slate-200 bg-white p-6">
        <h1 class="text-2xl font-bold text-slate-900">Tambah Buku</h1>

        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 grid gap-4 md:grid-cols-2">
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Gambar Utama (Opsional)</label>
                <input type="file" name="image_file" accept="image/*" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700">
                <p class="mt-1 text-xs text-slate-500">Jika diisi, file upload akan dipakai sebagai gambar utama.</p>
                @error('image_file') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Gambar Tambahan (Opsional)</label>
                <input type="file" name="image_files[]" accept="image/*" multiple class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700">
                @error('image_files') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                @error('image_files.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700">Preview Gambar</label>
                    <span class="text-xs text-slate-500">Preview dari URL dan file upload</span>
                </div>
                <div id="image-previews" class="grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
                <p class="mt-2 text-xs text-slate-500">File yang dipilih akan tampil sebagai preview sementara sebelum data disimpan.</p>
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
        const imagePreviewContainer = document.getElementById('image-previews');
        const mainImageInput = document.querySelector('input[name="image_url"]');
        const mainImageFileInput = document.querySelector('input[name="image_file"]');
        const extraImageFileInput = document.querySelector('input[name="image_files[]"]');

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

        function getSelectedFiles() {
            const files = [];

            if (mainImageFileInput?.files?.length) {
                files.push(...Array.from(mainImageFileInput.files));
            }

            if (extraImageFileInput?.files?.length) {
                files.push(...Array.from(extraImageFileInput.files));
            }

            return files;
        }

        function renderImagePreview() {
            const urls = getImageUrls();
            const files = getSelectedFiles();
            imagePreviewContainer.innerHTML = '';

            if (urls.length === 0 && files.length === 0) {
                imagePreviewContainer.innerHTML = '<div class="col-span-full rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500">Belum ada gambar untuk dipreview.</div>';
                return;
            }

            urls.forEach((url, index) => {
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-xl border border-slate-200 bg-white';
                card.innerHTML = `
                    <div class="h-24 w-full bg-slate-100">
                        <img src="${url}" alt="Preview URL ${index + 1}" class="h-full w-full object-cover" onerror="this.closest('div').classList.add('flex','items-center','justify-center'); this.replaceWith(Object.assign(document.createElement('span'), {className:'text-xs text-rose-600', textContent:'Gagal memuat URL'}));">
                    </div>
                    <p class="truncate px-2 py-1 text-[11px] text-slate-600" title="${url}">URL: ${url}</p>
                `;
                imagePreviewContainer.appendChild(card);
            });

            files.forEach((file, index) => {
                const temporaryUrl = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50';
                card.innerHTML = `
                    <div class="h-24 w-full bg-emerald-100">
                        <img src="${temporaryUrl}" alt="Preview file ${index + 1}" class="h-full w-full object-cover">
                    </div>
                    <p class="truncate px-2 py-1 text-[11px] text-emerald-700" title="${file.name}">FILE: ${file.name}</p>
                `;
                imagePreviewContainer.appendChild(card);
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
            wrapper.className = 'flex flex-col gap-2 sm:flex-row';
            wrapper.innerHTML = `
                <input type="url" name="image_urls[]" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm" placeholder="https://images.unsplash.com/extra-image.jpg">
                <button type="button" class="remove-image-field w-full rounded-xl border border-rose-300 px-4 py-2.5 text-sm font-semibold text-rose-600 sm:w-auto">Hapus</button>
            `;
            imageFieldsContainer.appendChild(wrapper);
            bindRemoveButtons();
            bindImageInputListeners();
            renderImagePreview();
        });

        function bindImageInputListeners() {
            imageFieldsContainer.querySelectorAll('input[name="image_urls[]"]').forEach(input => {
                input.addEventListener('input', renderImagePreview);
                input.addEventListener('change', renderImagePreview);
            });
        }

        mainImageInput?.addEventListener('input', renderImagePreview);
        mainImageInput?.addEventListener('change', renderImagePreview);
        mainImageFileInput?.addEventListener('change', renderImagePreview);
        extraImageFileInput?.addEventListener('change', renderImagePreview);
        bindImageInputListeners();
        bindRemoveButtons();
        renderImagePreview();
    </script>
@endsection
