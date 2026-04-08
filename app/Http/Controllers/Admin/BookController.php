<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $book->update($this->validatedBookData($request, $book));

        return redirect()->route('admin.books.index')->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil dihapus.');
    }

    private function validatedBookData(Request $request, ?Book $book = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'author' => ['required', 'string', 'max:150'],
            'price' => ['required', 'numeric', 'min:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'existing_image_urls' => ['nullable', 'array'],
            'existing_image_urls.*' => ['nullable', 'url', 'max:500'],
            'image_urls' => ['nullable', 'array'],
            'image_urls.*' => ['nullable', 'url', 'max:500'],
            'remove_existing_images' => ['nullable', 'array'],
            'remove_existing_images.*' => ['nullable', 'url', 'max:500'],
            'image_file' => ['nullable', 'image', 'max:3072'],
            'image_files' => ['nullable', 'array'],
            'image_files.*' => ['nullable', 'image', 'max:3072'],
            'description' => ['nullable', 'string'],
            'detail' => ['nullable', 'string'],
        ]);

        $existingExtraImages = $this->normalizeImageUrls($validated['existing_image_urls'] ?? []);
        $requestedRemovedImages = $this->normalizeImageUrls($validated['remove_existing_images'] ?? []);

        if ($requestedRemovedImages !== []) {
            $existingExtraImages = array_values(array_filter(
                $existingExtraImages,
                fn (string $url) => !in_array($url, $requestedRemovedImages, true)
            ));
        }

        $newExtraImages = $this->normalizeImageUrls($validated['image_urls'] ?? []);
        $extraImages = array_values(array_unique(array_merge($existingExtraImages, $newExtraImages)));

        if ($request->hasFile('image_file')) {
            $validated['image_url'] = $this->storeUploadedImage($request->file('image_file'), $request, $book?->image_url);
        }

        foreach ($request->file('image_files', []) as $uploadedImage) {
            if ($uploadedImage !== null) {
                $extraImages[] = $this->storeUploadedImage($uploadedImage, $request);
            }
        }

        $validated['image_urls'] = $extraImages;

        if (empty($validated['image_url']) && $extraImages !== []) {
            $validated['image_url'] = $extraImages[0];
        }

        if ($book !== null && $requestedRemovedImages !== []) {
            $finalImages = array_merge([$validated['image_url'] ?? null], $validated['image_urls']);

            foreach ($requestedRemovedImages as $removedUrl) {
                if (!in_array($removedUrl, $finalImages, true)) {
                    $this->deletePublicImageIfOwned($removedUrl);
                }
            }
        }

        unset(
            $validated['existing_image_urls'],
            $validated['remove_existing_images'],
            $validated['image_file'],
            $validated['image_files']
        );

        return $validated;
    }

    private function storeUploadedImage(\Illuminate\Http\UploadedFile $file, Request $request, ?string $previousImageUrl = null): string
    {
        if ($previousImageUrl !== null) {
            $this->deletePublicImageIfOwned($previousImageUrl);
        }

        $storedPath = $file->store('books', 'public');

        return $request->getSchemeAndHttpHost() . Storage::url($storedPath);
    }

    private function deletePublicImageIfOwned(string $imageUrl): void
    {
        $pathFromUrl = parse_url($imageUrl, PHP_URL_PATH);

        if (is_string($pathFromUrl) && str_starts_with($pathFromUrl, '/storage/')) {
            $path = ltrim(str_replace('/storage/', '', $pathFromUrl), '/');

            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }
        }
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
