<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    private const CART_SESSION_KEY = 'cart';

    private function cartPayload(array $cart, array $extra = []): array
    {
        $items = collect($cart)
            ->values()
            ->map(function (array $item): array {
                $item['subtotal'] = $item['price'] * $item['quantity'];

                return $item;
            })
            ->all();

        return array_merge([
            'items' => $items,
            'total' => $this->calculateCartTotal($cart),
            'cart_count' => $this->calculateCartCount($cart),
        ], $extra);
    }

    public function index(Request $request): View
    {
        $cart = $this->getCart($request);

        return view('cart.index', [
            'cart' => $cart,
            'total' => $this->calculateCartTotal($cart),
        ]);
    }

    public function add(Request $request, Book $book): RedirectResponse|Response
    {
        $cart = $this->getCart($request);

        if ((int) $book->stock <= 0) {
            $message = 'Stok buku habis, tidak bisa ditambahkan ke keranjang.';

            if ($request->expectsJson()) {
                return response()->json(array_merge($this->cartPayload($cart), [
                    'message' => $message,
                ]), 422);
            }

            return back()->with('error', $message);
        }

        $nextQuantity = isset($cart[$book->id]) ? ((int) $cart[$book->id]['quantity'] + 1) : 1;

        if ($nextQuantity > (int) $book->stock) {
            $message = 'Jumlah di keranjang melebihi stok tersedia (' . $book->stock . ').';

            if ($request->expectsJson()) {
                return response()->json(array_merge($this->cartPayload($cart), [
                    'message' => $message,
                ]), 422);
            }

            return back()->with('error', $message);
        }

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

        $this->saveCart($request, $cart);

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

        $cart = $this->getCart($request);
        $removed = false;

        if (isset($cart[$bookId])) {
            if ($validated['quantity'] <= 0) {
                unset($cart[$bookId]);
                $this->saveCart($request, $cart);
                $removed = true;

                if ($request->expectsJson()) {
                    return response()->json($this->cartPayload($cart, [
                        'removed' => true,
                        'book_id' => $bookId,
                    ]));
                }

                return back()->with('success', 'Item di keranjang berhasil dihapus.');
            }

            $book = Book::query()->find($bookId);

            if ($book === null || (int) $book->stock <= 0) {
                $message = 'Buku sudah tidak tersedia, silakan hapus dari keranjang.';

                if ($request->expectsJson()) {
                    return response()->json(array_merge($this->cartPayload($cart), [
                        'message' => $message,
                        'book_id' => $bookId,
                    ]), 422);
                }

                return back()->with('error', $message);
            }

            if ($validated['quantity'] > (int) $book->stock) {
                $message = 'Stok tersisa ' . $book->stock . ' untuk buku ini.';

                if ($request->expectsJson()) {
                    return response()->json(array_merge($this->cartPayload($cart), [
                        'message' => $message,
                        'book_id' => $bookId,
                        'max_quantity' => (int) $book->stock,
                    ]), 422);
                }

                return back()->with('error', $message);
            }

            $cart[$bookId]['quantity'] = $validated['quantity'];
            $this->saveCart($request, $cart);
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
        $cart = $this->getCart($request);
        unset($cart[$bookId]);
        $this->saveCart($request, $cart);

        if ($request->expectsJson()) {
            return response()->json($this->cartPayload($cart, [
                'removed' => true,
                'book_id' => $bookId,
            ]));
        }

        return back()->with('success', 'Item di keranjang berhasil dihapus.');
    }

    private function getCart(Request $request): array
    {
        return $request->session()->get(self::CART_SESSION_KEY, []);
    }

    private function saveCart(Request $request, array $cart): void
    {
        $request->session()->put(self::CART_SESSION_KEY, $cart);
    }

    private function calculateCartTotal(array $cart): float
    {
        return (float) collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);
    }

    private function calculateCartCount(array $cart): int
    {
        return (int) collect($cart)->sum('quantity');
    }
}
