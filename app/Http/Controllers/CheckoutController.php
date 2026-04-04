<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Simpan checkout COD dari keranjang ke tabel orders.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong, isi dulu ya.');
        }

        DB::transaction(function () use ($request, $validated, $cart): void {
            $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'total_price' => $total,
                'payment_method' => 'COD',
                'status' => 'Menunggu Konfirmasi',
                'phone' => $validated['phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($cart as $item) {
                $book = Book::findOrFail($item['book_id']);
                $subtotal = $item['quantity'] * $item['price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal,
                ]);
            }
        });

        $request->session()->forget('cart');

        return redirect()->route('orders.index')->with('success', 'Checkout COD berhasil, pesanan kamu sudah kami catat.');
    }

    /**
     * Tampilkan riwayat order user yang sedang login.
     */
    public function index(Request $request): View
    {
        $orders = Order::with('items.book')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(8);

        return view('orders.index', ['orders' => $orders]);
    }
}
