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
    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::with('category')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Book::create($this->validatedBookData($request));

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $book->update($this->validatedBookData($request));

        return redirect()->route('admin.books.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil dihapus.');
    }

    private function validatedBookData(Request $request): array
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

        $extraImages = $this->normalizeImageUrls($validated['image_urls'] ?? []);

        $validated['image_urls'] = $extraImages;

        if (empty($validated['image_url']) && $extraImages !== []) {
            $validated['image_url'] = $extraImages[0];
        }

        return $validated;
    }

    private function normalizeImageUrls(array $imageUrls): array
    {
        return collect($imageUrls)
            ->filter(fn ($url) => is_string($url) && trim($url) !== '')
            ->map(fn ($url) => trim($url))
            ->values()
            ->all();
    }
}
