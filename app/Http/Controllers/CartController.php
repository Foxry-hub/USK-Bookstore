<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    private function cartPayload(array $cart, array $extra = []): array
    {
        $items = collect($cart)->values()->map(function (array $item): array {
            $item['subtotal'] = $item['price'] * $item['quantity'];

            return $item;
        })->all();

        return array_merge([
            'items' => $items,
            'total' => collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']),
            'cart_count' => collect($cart)->sum('quantity'),
        ], $extra);
    }

    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);

        return view('cart.index', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']),
        ]);
    }

    public function add(Request $request, Book $book): RedirectResponse|Response
    {
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$book->id])) {
            $cart[$book->id]['quantity']++;
        } else {
            $cart[$book->id] = [
                'book_id' => $book->id,
                'title' => $book->title,
                'price' => (float) $book->price,
                'image_url' => $book->image_url,
                'quantity' => 1,
            ];
        }

        $request->session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, [
                'message' => 'Buku berhasil masuk keranjang.',
                'book_id' => $book->id,
            ]));
        }

        return back()->with('success', 'Buku berhasil masuk keranjang.');
    }

    public function update(Request $request, int $bookId): RedirectResponse|Response
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'max:99'],
        ]);

        $cart = $request->session()->get('cart', []);
        $removed = false;

        if (isset($cart[$bookId])) {
            if ($validated['quantity'] <= 0) {
                unset($cart[$bookId]);
                $request->session()->put('cart', $cart);
                $removed = true;

                if ($request->expectsJson()) {
                    return response()->json($this->cartPayload($cart, [
                        'removed' => true,
                        'book_id' => $bookId,
                    ]));
                }

                return back()->with('success', 'Item di keranjang berhasil dihapus.');
            }

            $cart[$bookId]['quantity'] = $validated['quantity'];
            $request->session()->put('cart', $cart);
        }

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, [
                'removed' => $removed,
                'book_id' => $bookId,
                'quantity' => $cart[$bookId]['quantity'] ?? null,
            ]));
        }

        return back()->with('success', 'Jumlah item keranjang diperbarui.');
    }

    public function remove(Request $request, int $bookId): RedirectResponse|Response
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$bookId]);
        $request->session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, [
                'removed' => true,
                'book_id' => $bookId,
            ]));
        }

        return back()->with('success', 'Item di keranjang berhasil dihapus.');
    }
}
