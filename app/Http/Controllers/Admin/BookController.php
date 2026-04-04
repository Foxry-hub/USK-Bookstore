<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * Tampilkan daftar buku untuk dashboard admin.
     */
    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::with('category')->latest()->paginate(10),
        ]);
    }

    /**
     * Tampilkan form tambah buku baru.
     */
    public function create(): View
    {
        return view('admin.books.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Simpan data buku dari form admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'author' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'detail' => ['nullable', 'string'],
        ]);

        $extraImages = collect($validated['image_urls'] ?? [])
            ->filter(fn ($url) => is_string($url) && trim($url) !== '')
            ->map(fn ($url) => trim($url))
            ->values()
            ->all();

        $validated['image_urls'] = $extraImages;

        if (empty($validated['image_url']) && count($extraImages) > 0) {
            $validated['image_url'] = $extraImages[0];
        }

        Book::create($validated);

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }

    /**
     * Tampilkan form edit buku.
     */
    public function edit(Book $book): View
    {
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Simpan update buku dari admin.
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'author' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string'],
            'detail' => ['nullable', 'string'],
        ]);

        $extraImages = collect($validated['image_urls'] ?? [])
            ->filter(fn ($url) => is_string($url) && trim($url) !== '')
            ->map(fn ($url) => trim($url))
            ->values()
            ->all();

        $validated['image_urls'] = $extraImages;

        if (empty($validated['image_url']) && count($extraImages) > 0) {
            $validated['image_url'] = $extraImages[0];
        }

        $book->update($validated);

        return redirect()->route('admin.books.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    /**
     * Hapus buku dari katalog.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil dihapus.');
    }
}
