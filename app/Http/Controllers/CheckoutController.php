<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    /**
     * Simpan checkout dari keranjang ke tabel orders.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:COD,MIDTRANS'],
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong, isi dulu ya.');
        }

        $paymentMethod = $validated['payment_method'];

        $order = DB::transaction(function () use ($request, $validated, $cart, $paymentMethod): Order {
            $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'total_price' => $total,
                'payment_method' => $paymentMethod,
                'status' => $paymentMethod === 'MIDTRANS' ? 'Menunggu Pembayaran' : 'Menunggu Konfirmasi',
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

            return $order;
        });

        if ($paymentMethod === 'MIDTRANS') {
            try {
                $midtransRedirectUrl = $this->createMidtransTransaction(
                    $order,
                    $this->mapCartToMidtransItems($cart),
                    $request->user(),
                    $validated['phone'],
                );
            } catch (\Throwable $exception) {
                Log::error('Midtrans checkout failed', [
                    'order_id' => $order->id,
                    'message' => $exception->getMessage(),
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('error', 'Pesanan berhasil dibuat, tapi gagal membuka halaman pembayaran Midtrans. Silakan klik Bayar Ulang dari riwayat pesanan.');
            }
        }

        $request->session()->forget('cart');

        if ($paymentMethod === 'MIDTRANS') {
            return redirect()->away($midtransRedirectUrl)->with('success', 'Pesanan dibuat. Lanjutkan pembayaran di Midtrans ya.');
        }

        return redirect()->route('orders.index')->with('success', 'Checkout COD berhasil, pesanan kamu sudah kami catat.');
    }

    /**
     * Bayar ulang order Midtrans dari halaman riwayat pesanan.
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->payment_method === 'COD') {
            return back()->with('error', 'Order COD tidak perlu pembayaran online.');
        }

        if (! in_array($order->status, ['Menunggu Pembayaran', 'Pembayaran Gagal'], true)) {
            return back()->with('error', 'Order ini tidak bisa dibayar ulang karena statusnya sudah berubah.');
        }

        $itemDetails = $this->mapOrderItemsToMidtransItems($order);

        if ($itemDetails === []) {
            return back()->with('error', 'Gagal bayar ulang karena item order tidak ditemukan.');
        }

        try {
            $midtransRedirectUrl = $this->createMidtransTransaction(
                $order,
                $itemDetails,
                $request->user(),
                $order->phone,
            );
        } catch (\Throwable $exception) {
            Log::error('Midtrans retry payment failed', [
                'order_id' => $order->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat pembayaran ulang Midtrans. Coba lagi sebentar lagi.');
        }

        return redirect()->away($midtransRedirectUrl);
    }

    /**
     * Endpoint callback Midtrans untuk update status pembayaran order.
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();

        $midtransOrderId = $payload['order_id'] ?? null;
        $originalOrderCode = $payload['custom_field1'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if ($midtransOrderId === null || $statusCode === null || $grossAmount === null || $signatureKey === null) {
            return response()->json(['message' => 'Payload tidak valid.'], 422);
        }

        $serverKey = (string) config('services.midtrans.server_key');
        $localSignature = hash('sha512', $midtransOrderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($localSignature, (string) $signatureKey)) {
            Log::warning('Midtrans notification signature mismatch', ['order_id' => $midtransOrderId]);

            return response()->json(['message' => 'Signature tidak valid.'], 403);
        }

        $order = Order::query()
            ->when(
                is_string($originalOrderCode) && $originalOrderCode !== '',
                fn ($query) => $query->where('order_code', $originalOrderCode),
                fn ($query) => $query->where('midtrans_order_id', $midtransOrderId),
            )
            ->first();

        if ($order === null) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $paymentType = (string) ($payload['payment_type'] ?? 'MIDTRANS');

        $nextStatus = match ($transactionStatus) {
            'settlement' => 'Dibayar',
            'capture' => $fraudStatus === 'challenge' ? 'Menunggu Verifikasi' : 'Dibayar',
            'pending' => 'Menunggu Pembayaran',
            'deny', 'cancel', 'expire' => 'Pembayaran Gagal',
            'refund', 'partial_refund' => 'Refund',
            default => $order->status,
        };

        $order->update([
            'status' => $nextStatus,
            'payment_method' => strtoupper($paymentType),
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_payment_type' => $paymentType,
            'midtrans_fraud_status' => $fraudStatus ?: null,
            'paid_at' => in_array($transactionStatus, ['settlement', 'capture'], true)
                ? ($order->paid_at ?? now())
                : $order->paid_at,
        ]);

        return response()->json(['message' => 'OK']);
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

    /**
     * Set konfigurasi Midtrans berdasarkan environment.
     */
    private function configureMidtrans(): void
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum di-set di environment.');
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Buat transaksi Snap Midtrans dan simpan token + midtrans order id terbaru.
     *
     * @param array<int, array{id: string, price: int, quantity: int, name: string}> $itemDetails
     */
    private function createMidtransTransaction(Order $order, array $itemDetails, User $user, string $phone): string
    {
        $this->configureMidtrans();

        $midtransOrderId = $this->generateMidtransOrderId($order);

        $snapResponse = Snap::createTransaction([
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) round($order->total_price),
            ],
            'enabled_payments' => [
                'bank_transfer',
                'bca_va',
                'bni_va',
                'bri_va',
                'gopay',
                'qris',
                'shopeepay',
                'dana',
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $phone,
            ],
            'callbacks' => [
                'finish' => route('orders.index'),
                'unfinish' => route('orders.index'),
                'error' => route('orders.index'),
            ],
            'custom_field1' => $order->order_code,
            'item_details' => $itemDetails,
        ]);

        $redirectUrl = $snapResponse->redirect_url ?? null;

        if (! is_string($redirectUrl) || $redirectUrl === '') {
            throw new \RuntimeException('Gagal membuat transaksi Midtrans. Redirect URL tidak tersedia.');
        }

        $order->update([
            'status' => 'Menunggu Pembayaran',
            'payment_method' => 'MIDTRANS',
            'midtrans_transaction_id' => $snapResponse->token ?? null,
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_transaction_status' => 'pending',
        ]);

        return $redirectUrl;
    }

    /**
     * @param array<int, array{book_id: int, price: float|int, quantity: int, title: string}> $cart
     * @return array<int, array{id: string, price: int, quantity: int, name: string}>
     */
    private function mapCartToMidtransItems(array $cart): array
    {
        return collect($cart)
            ->map(fn (array $item): array => [
                'id' => (string) $item['book_id'],
                'price' => (int) round($item['price']),
                'quantity' => (int) $item['quantity'],
                'name' => str($item['title'])->limit(50)->toString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, price: int, quantity: int, name: string}>
     */
    private function mapOrderItemsToMidtransItems(Order $order): array
    {
        return $order->items()
            ->with('book')
            ->get()
            ->map(function (OrderItem $item): array {
                return [
                    'id' => (string) $item->book_id,
                    'price' => (int) round($item->price),
                    'quantity' => (int) $item->quantity,
                    'name' => str(optional($item->book)->title ?? 'Book')->limit(50)->toString(),
                ];
            })
            ->values()
            ->all();
    }

    private function generateMidtransOrderId(Order $order): string
    {
        return $order->order_code . '-MT-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }
}
