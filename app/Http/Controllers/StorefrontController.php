<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('store.index', [
            'featuredBooks' => Book::query()->with('category')->latest()->take(4)->get(),
        ]);
    }

    public function catalog(Request $request): View
    {
        $query = Book::query()->with('category')->latest();

        if ($request->filled('search')) {
            $keyword = $request->string('search')->toString();

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->input('category'));
        }

        $books = $query->get();

        $groupedCategories = $books
            ->groupBy('category_id')
            ->map(function ($items): array {
                $first = $items->first();

                return [
                    'id' => $first->category?->id,
                    'name' => $first->category?->name ?? 'Tanpa Kategori',
                    'books' => $items->values(),
                ];
            })
            ->sortBy('name')
            ->values();

        $activeCategoryId = $request->filled('category') ? (int) $request->input('category') : null;
        $activeCategoryName = null;

        if ($activeCategoryId) {
            $activeCategoryName = Category::query()->whereKey($activeCategoryId)->value('name');
        }

        return view('store.catalog', [
            'groupedCategories' => $groupedCategories,
            'booksCount' => $books->count(),
            'activeCategoryId' => $activeCategoryId,
            'activeCategoryName' => $activeCategoryName,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Book $book): View
    {
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->latest()
            ->take(4)
            ->get();

        return view('store.show', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }
}
