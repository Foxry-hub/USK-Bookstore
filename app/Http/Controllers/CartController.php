<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    /**
     * Tampilkan isi keranjang yang disimpan di session.
     */
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);

        return view('cart.index', [
            'cart' => $cart,
            'total' => collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']),
        ]);
    }

    /**
     * Tambah buku ke cart, kalau udah ada tinggal nambah jumlahnya.
     */
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

        return back()->with('success', 'Buku berhasil masuk keranjang.');
    }

    /**
     * Update jumlah item kalau user mau nambah/kurangin pesanan.
     */
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

                $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'removed' => true,
                        'book_id' => $bookId,
                        'total' => $total,
                        'cart_count' => count($cart),
                    ]);
                }

                return back()->with('success', 'Item di keranjang berhasil dihapus.');
            }

            $cart[$bookId]['quantity'] = $validated['quantity'];
            $request->session()->put('cart', $cart);
        }

        $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'removed' => $removed,
                'book_id' => $bookId,
                'quantity' => $cart[$bookId]['quantity'] ?? null,
                'total' => $total,
                'cart_count' => count($cart),
            ]);
        }

        return back()->with('success', 'Jumlah item keranjang diperbarui.');
    }

    /**
     * Hapus item tertentu dari cart.
     */
    public function remove(Request $request, int $bookId): RedirectResponse|Response
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$bookId]);
        $request->session()->put('cart', $cart);

        $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'removed' => true,
                'book_id' => $bookId,
                'total' => $total,
                'cart_count' => count($cart),
            ]);
        }

        return back()->with('success', 'Item di keranjang berhasil dihapus.');
    }
}
