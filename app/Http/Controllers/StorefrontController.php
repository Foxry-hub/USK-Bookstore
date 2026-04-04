<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Landing page + katalog buku dengan fitur search.
     */
    public function index(Request $request): View
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

        return view('store.index', [
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
