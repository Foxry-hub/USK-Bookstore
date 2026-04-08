<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:COD,MIDTRANS'],
            'payment_detail' => ['required', 'in:card,cod'],
        ]);

        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong, isi dulu ya.');
        }

        $paymentMethod = $validated['payment_method'];
        $paymentDetail = $validated['payment_detail'];

        $order = DB::transaction(function () use ($request, $validated, $cart, $paymentMethod, $paymentDetail): Order {
            $total = collect($cart)->sum(fn (array $item): float => $item['price'] * $item['quantity']);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'total_price' => $total,
                'payment_method' => $paymentMethod,
                'midtrans_payment_type' => $paymentMethod === 'MIDTRANS' ? $paymentDetail : null,
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

            return $order;
        });

        if ($paymentMethod === 'MIDTRANS') {
            try {
                $midtransRedirectUrl = $this->createMidtransTransaction(
                    $order,
                    $this->mapCartToMidtransItems($cart),
                    $request->user(),
                    $validated['phone'],
                    $paymentDetail,
                );
            } catch (\Throwable $exception) {
                Log::error('Midtrans checkout failed', [
                    'order_id' => $order->id,
                    'payment_detail' => $paymentDetail,
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                ]);

                $errorMessage = 'Pesanan berhasil dibuat, tapi gagal membuka halaman pembayaran Midtrans. Silakan klik Bayar Ulang dari riwayat pesanan.';

                if (app()->isLocal()) {
                    $errorMessage .= ' Detail: ' . $exception->getMessage();
                }

                return redirect()
                    ->route('orders.index')
                    ->with('error', $errorMessage);
            }
        }

        $request->session()->forget('cart');

        if ($paymentMethod === 'MIDTRANS') {
            return redirect()->away($midtransRedirectUrl)->with('success', 'Pesanan dibuat. Lanjutkan pembayaran di Midtrans ya.');
        }

        return redirect()->route('orders.index')->with('success', 'Checkout COD berhasil, pesanan kamu sudah kami catat.');
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->payment_method === 'COD') {
            return back()->with('error', 'Order COD tidak perlu pembayaran online.');
        }

        if (! in_array($order->midtrans_transaction_status, ['pending', 'deny', 'cancel', 'expire'], true)
            && ! in_array($order->status, ['Menunggu Pembayaran', 'Pembayaran Gagal'], true)
        ) {
            return back()->with('error', 'Order ini tidak bisa dibayar ulang karena statusnya sudah berubah.');
        }

        $itemDetails = $this->mapOrderItemsToMidtransItems($order);

        if ($itemDetails === []) {
            return back()->with('error', 'Gagal bayar ulang karena item order tidak ditemukan.');
        }

        try {
            $retryPaymentDetail = 'card';

            $midtransRedirectUrl = $this->createMidtransTransaction(
                $order,
                $itemDetails,
                $request->user(),
                $order->phone,
                $retryPaymentDetail,
            );
        } catch (\Throwable $exception) {
            Log::error('Midtrans retry payment failed', [
                'order_id' => $order->id,
                'payment_detail' => $order->midtrans_payment_type,
                'message' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            $errorMessage = 'Gagal membuat pembayaran ulang Midtrans. Coba lagi sebentar lagi.';

            if (app()->isLocal()) {
                $errorMessage .= ' Detail: ' . $exception->getMessage();
            }

            return back()->with('error', $errorMessage);
        }

        return redirect()->away($midtransRedirectUrl);
    }

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

        $nextStatus = $this->resolveOrderStatusFromPaymentStatus($order, $transactionStatus, $fraudStatus);

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

    public function index(Request $request): View
    {
        $this->autoFinalizeDueOrders($request->user()->id);
        $this->syncOrderFromGatewayRedirect($request);

        $activePackageStatus = (string) $request->query('status', 'all');
        $statusFilters = [
            'all' => null,
            'menunggu_konfirmasi' => ['Menunggu Konfirmasi', 'Menunggu Verifikasi'],
            'dibayar' => ['Dibayar'],
            'diproses' => ['Diproses'],
            'dikirim' => ['Dikirim'],
            'selesai' => ['Selesai'],
            'dibatalkan' => ['Pembayaran Gagal'],
        ];

        $ordersQuery = Order::with('items.book')
            ->where('user_id', $request->user()->id)
            ->latest();

        if ($activePackageStatus !== 'all' && isset($statusFilters[$activePackageStatus])) {
            $ordersQuery->whereIn('status', $statusFilters[$activePackageStatus]);
        }

        $orders = $ordersQuery->paginate(8)->withQueryString();

        $packageStats = [
            'all' => Order::where('user_id', $request->user()->id)->count(),
            'menunggu_konfirmasi' => Order::where('user_id', $request->user()->id)
                ->whereIn('status', ['Menunggu Konfirmasi', 'Menunggu Verifikasi'])
                ->count(),
            'dibayar' => Order::where('user_id', $request->user()->id)->where('status', 'Dibayar')->count(),
            'diproses' => Order::where('user_id', $request->user()->id)->where('status', 'Diproses')->count(),
            'dikirim' => Order::where('user_id', $request->user()->id)->where('status', 'Dikirim')->count(),
            'selesai' => Order::where('user_id', $request->user()->id)->where('status', 'Selesai')->count(),
            'dibatalkan' => Order::where('user_id', $request->user()->id)->where('status', 'Pembayaran Gagal')->count(),
        ];

        return view('orders.index', [
            'orders' => $orders,
            'activePackageStatus' => $activePackageStatus,
            'packageStats' => $packageStats,
        ]);
    }

    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'Dikirim') {
            return back()->with('error', 'Pesanan belum bisa dikonfirmasi selesai.');
        }

        if ($order->estimated_delivery_at === null || now()->lt($order->estimated_delivery_at)) {
            return back()->with('error', 'Pesanan belum masuk estimasi sampai.');
        }

        $order->update([
            'status' => 'Selesai',
            'received_at' => now(),
            'paid_at' => $order->payment_method === 'COD' ? ($order->paid_at ?? now()) : $order->paid_at,
        ]);

        return back()->with('success', 'Terima kasih, pesanan sudah ditandai selesai.');
    }

    public function downloadInvoice(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->loadMissing(['user', 'items.book']);

        $itemsTotal = (float) $order->items->sum('subtotal');
        $grandTotal = (float) $order->total_price;

        $pdf = Pdf::loadView('orders.invoice', [
            'order' => $order,
            'invoiceNumber' => $this->generateInvoiceNumber($order),
            'invoiceDate' => $order->created_at,
            'itemsTotal' => $itemsTotal,
            'grandTotal' => $grandTotal,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('invoice-' . $order->order_code . '.pdf');
    }

    private function syncOrderFromGatewayRedirect(Request $request): void
    {
        $midtransOrderId = (string) $request->query('order_id', '');

        if ($midtransOrderId === '') {
            return;
        }

        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($midtransOrderId): void {
                $query->where('midtrans_order_id', $midtransOrderId)
                    ->orWhere('order_code', $midtransOrderId);
            })
            ->first();

        if ($order === null) {
            return;
        }

        try {
            $this->configureMidtrans();

            $authHeader = base64_encode((string) config('services.midtrans.server_key') . ':');

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $authHeader,
            ])->get('https://api.sandbox.midtrans.com/v2/' . $midtransOrderId . '/status');

            if (! $response->successful()) {
                return;
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                return;
            }

            $transactionStatus = (string) ($payload['transaction_status'] ?? '');
            $fraudStatus = (string) ($payload['fraud_status'] ?? '');
            $paymentType = (string) ($payload['payment_type'] ?? 'MIDTRANS');

            $nextStatus = $this->resolveOrderStatusFromPaymentStatus($order, $transactionStatus, $fraudStatus);

            $order->update([
                'status' => $nextStatus,
                'payment_method' => strtoupper($paymentType),
                'midtrans_order_id' => (string) ($payload['order_id'] ?? $midtransOrderId),
                'midtrans_transaction_status' => $transactionStatus ?: $order->midtrans_transaction_status,
                'midtrans_payment_type' => $paymentType ?: $order->midtrans_payment_type,
                'midtrans_fraud_status' => $fraudStatus ?: $order->midtrans_fraud_status,
                'paid_at' => in_array($transactionStatus, ['settlement', 'capture'], true)
                    ? ($order->paid_at ?? now())
                    : $order->paid_at,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to sync Midtrans status from redirect', [
                'order_id' => $midtransOrderId,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function autoFinalizeDueOrders(int $userId): void
    {
        Order::query()
            ->where('user_id', $userId)
            ->where('status', 'Dikirim')
            ->where('payment_method', '!=', 'COD')
            ->whereNotNull('estimated_delivery_at')
            ->where('estimated_delivery_at', '<=', now())
            ->update([
                'status' => 'Selesai',
                'received_at' => DB::raw('COALESCE(received_at, CURRENT_TIMESTAMP)'),
            ]);
    }

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

    private function createMidtransTransaction(Order $order, array $itemDetails, User $user, string $phone, string $paymentDetail): string
    {
        $this->configureMidtrans();

        $midtransOrderId = $this->generateMidtransOrderId($order);
        $enabledPayments = $this->resolveMidtransEnabledPayments($paymentDetail);

        $snapResponse = Snap::createTransaction([
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) round($order->total_price),
            ],
            'enabled_payments' => $enabledPayments,
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
            'status' => 'Menunggu Konfirmasi',
            'payment_method' => 'MIDTRANS',
            'midtrans_payment_type' => $paymentDetail,
            'midtrans_transaction_id' => $snapResponse->token ?? null,
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_transaction_status' => 'pending',
        ]);

        return $redirectUrl;
    }

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

    private function resolveMidtransEnabledPayments(string $paymentDetail): array
    {
        return match ($paymentDetail) {
            'card' => ['credit_card'],
            default => throw new \InvalidArgumentException('Metode pembayaran Midtrans tidak didukung.'),
        };
    }

    private function resolveOrderStatusFromPaymentStatus(Order $order, string $transactionStatus, string $fraudStatus): string
    {
        if (in_array($order->status, ['Diproses', 'Dikirim', 'Selesai'], true)) {
            return $order->status;
        }

        return match ($transactionStatus) {
            'settlement' => 'Dibayar',
            'capture' => $fraudStatus === 'challenge' ? 'Menunggu Verifikasi' : 'Dibayar',
            'pending' => 'Menunggu Konfirmasi',
            'deny', 'cancel', 'expire' => 'Pembayaran Gagal',
            'refund', 'partial_refund' => 'Refund',
            default => $order->status,
        };
    }

    private function generateInvoiceNumber(Order $order): string
    {
        return 'INV-' . $order->created_at->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }
}
