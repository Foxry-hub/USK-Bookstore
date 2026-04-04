<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Landing page dengan preview katalog.
     */
    public function index(): View
    {
        return view('store.index', [
            'featuredBooks' => Book::query()->with('category')->latest()->take(4)->get(),
            'totalBooks' => Book::count(),
            'categoriesCount' => Category::count(),
        ]);
    }

    /**
     * Halaman katalog lengkap dengan filter.
     */
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

        return view('store.catalog', [
            'books' => $query->paginate(8)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Detail page produk buku dengan preview lengkap.
     */
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
