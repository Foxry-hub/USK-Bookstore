<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    private const CART_SESSION_KEY = 'cart';

    private const PAYMENT_COD = 'COD';

    private const PAYMENT_MIDTRANS = 'MIDTRANS';

    private const PAYMENT_CASH = 'CASH';

    private const ORDER_STATUS_WAITING_CONFIRMATION = 'Menunggu Konfirmasi';

    private const ORDER_STATUS_WAITING_VERIFICATION = 'Menunggu Verifikasi';

    private const ORDER_STATUS_WAITING_PAYMENT = 'Menunggu Pembayaran';

    private const ORDER_STATUS_PAID = 'Dibayar';

    private const ORDER_STATUS_PROCESSED = 'Diproses';

    private const ORDER_STATUS_SHIPPED = 'Dikirim';

    private const ORDER_STATUS_DONE = 'Selesai';

    private const ORDER_STATUS_FAILED = 'Pembayaran Gagal';

    private const ORDER_STATUS_REFUND = 'Refund';

    private const FINALIZED_ORDER_STATUSES = [
        self::ORDER_STATUS_PROCESSED,
        self::ORDER_STATUS_SHIPPED,
        self::ORDER_STATUS_DONE,
    ];

    private const RETRYABLE_MIDTRANS_STATUSES = ['pending', 'deny', 'cancel', 'expire'];

    private const RETRYABLE_ORDER_STATUSES = [
        self::ORDER_STATUS_WAITING_PAYMENT,
        self::ORDER_STATUS_FAILED,
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCheckoutPayload($request);
        $cart = $request->session()->get(self::CART_SESSION_KEY, []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong, isi dulu ya.');
        }

        $paymentMethod = (string) $validated['payment_method'];
        $paymentDetail = (string) $validated['payment_detail'];

        $order = $this->createOrderFromCart($request, $validated, $cart);
        $midtransRedirectUrl = null;

        if ($paymentMethod === self::PAYMENT_MIDTRANS) {
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

        $request->session()->forget(self::CART_SESSION_KEY);

        if ($midtransRedirectUrl !== null) {
            return redirect()->away($midtransRedirectUrl)->with('success', 'Pesanan berhasil dibuat.');
        }

        $successMessage = $paymentMethod === self::PAYMENT_CASH 
            ? 'Checkout berhasil, pesanan kamu sudah kami catat. Admin akan memproses pembayaran tunai kamu.'
            : 'Checkout COD berhasil, pesanan kamu sudah kami catat.';

        return redirect()->route('orders.index')->with('success', $successMessage);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->abortIfNotOrderOwner($request, $order);

        if ($order->payment_method === self::PAYMENT_COD) {
            return back()->with('error', 'Order COD tidak perlu pembayaran online.');
        }

        if (! $this->canRetryMidtransPayment($order)) {
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
        $paymentType = (string) ($payload['payment_type'] ?? self::PAYMENT_MIDTRANS);

        $this->syncPaymentResultToOrder($order, (string) $midtransOrderId, $transactionStatus, $fraudStatus, $paymentType);

        return response()->json(['message' => 'OK']);
    }

    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $this->autoFinalizeDueOrders($userId);
        $this->syncOrderFromGatewayRedirect($request);

        $activePackageStatus = (string) $request->query('status', 'all');
        $statusFilters = $this->orderStatusFilters();

        $ordersQuery = Order::with('items.book')
            ->where('user_id', $userId)
            ->latest();

        if ($activePackageStatus !== 'all' && isset($statusFilters[$activePackageStatus])) {
            $ordersQuery->whereIn('status', $statusFilters[$activePackageStatus]);
        }

        $orders = $ordersQuery->paginate(8)->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'activePackageStatus' => $activePackageStatus,
            'packageStats' => $this->buildPackageStats($userId, $statusFilters),
        ]);
    }

    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        $this->abortIfNotOrderOwner($request, $order);

        if ($order->status !== self::ORDER_STATUS_SHIPPED) {
            return back()->with('error', 'Pesanan belum bisa dikonfirmasi selesai.');
        }

        if ($order->estimated_delivery_at === null || now()->lt($order->estimated_delivery_at)) {
            return back()->with('error', 'Pesanan belum masuk estimasi sampai.');
        }

        $order->update([
            'status' => self::ORDER_STATUS_DONE,
            'received_at' => now(),
            'paid_at' => $order->payment_method === self::PAYMENT_COD ? ($order->paid_at ?? now()) : $order->paid_at,
        ]);

        return back()->with('success', 'Terima kasih, pesanan sudah ditandai selesai.');
    }

    public function confirmNotReceived(Request $request, Order $order): RedirectResponse
    {
        $this->abortIfNotOrderOwner($request, $order);

        if ($order->payment_method !== self::PAYMENT_COD) {
            return back()->with('error', 'Fitur ini khusus untuk pesanan COD.');
        }

        if ($order->status !== self::ORDER_STATUS_SHIPPED) {
            return back()->with('error', 'Pesanan belum bisa dikonfirmasi.');
        }

        if ($order->estimated_delivery_at === null || now()->lt($order->estimated_delivery_at)) {
            return back()->with('error', 'Pesanan belum masuk estimasi sampai.');
        }

        $order->update([
            'status' => self::ORDER_STATUS_FAILED,
            'received_at' => now(),
        ]);

        return back()->with('success', 'Laporan tidak diterima sudah kami catat. Tim kami akan menindaklanjuti.');
    }

    public function downloadInvoice(Request $request, Order $order)
    {
        $this->abortIfNotOrderOwner($request, $order);

        $order->loadMissing(['user', 'items.book']);

        $grandTotal = (float) $order->total_price;

        $pdf = Pdf::loadView('orders.invoice', [
            'order' => $order,
            'invoiceNumber' => $this->generateInvoiceNumber($order),
            'invoiceDate' => $order->created_at,
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
            $paymentType = (string) ($payload['payment_type'] ?? self::PAYMENT_MIDTRANS);

            $this->syncPaymentResultToOrder(
                $order,
                (string) ($payload['order_id'] ?? $midtransOrderId),
                $transactionStatus !== '' ? $transactionStatus : (string) $order->midtrans_transaction_status,
                $fraudStatus !== '' ? $fraudStatus : (string) $order->midtrans_fraud_status,
                $paymentType !== '' ? $paymentType : (string) $order->midtrans_payment_type,
            );
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
            ->where('status', self::ORDER_STATUS_SHIPPED)
            ->where('payment_method', '!=', self::PAYMENT_COD)
            ->whereNotNull('estimated_delivery_at')
            ->where('estimated_delivery_at', '<=', now())
            ->update([
                'status' => self::ORDER_STATUS_DONE,
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

        // Begitu token jadi, order kita set fix ke jalur Midtrans biar konsisten.
        $order->update([
            'status' => self::ORDER_STATUS_WAITING_CONFIRMATION,
            'payment_method' => self::PAYMENT_MIDTRANS,
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
        // Kalau order udah masuk fase operasional, status payment gak boleh ngacak status ini lagi.
        if (in_array($order->status, self::FINALIZED_ORDER_STATUSES, true)) {
            return $order->status;
        }

        return match ($transactionStatus) {
            'settlement' => self::ORDER_STATUS_PAID,
            'capture' => $fraudStatus === 'challenge' ? self::ORDER_STATUS_WAITING_VERIFICATION : self::ORDER_STATUS_PAID,
            'pending' => self::ORDER_STATUS_WAITING_CONFIRMATION,
            'deny', 'cancel', 'expire' => self::ORDER_STATUS_FAILED,
            'refund', 'partial_refund' => self::ORDER_STATUS_REFUND,
            default => $order->status,
        };
    }

    private function generateInvoiceNumber(Order $order): string
    {
        return 'INV-' . $order->created_at->format('Ymd') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    private function validateCheckoutPayload(Request $request): array
    {
        return $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:COD,MIDTRANS,CASH'],
            'payment_detail' => ['required', 'in:card,cod,cash'],
        ]);
    }

    private function createOrderFromCart(Request $request, array $validated, array $cart): Order
    {
        return DB::transaction(function () use ($request, $validated, $cart): Order {
            $paymentMethod = (string) $validated['payment_method'];
            $paymentDetail = (string) $validated['payment_detail'];

            $bookIds = collect($cart)->pluck('book_id')->map(fn ($id) => (int) $id)->all();
            $books = Book::query()
                ->whereIn('id', $bookIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $normalizedCartItems = [];
            $computedOrderTotal = 0.0;

            foreach ($cart as $item) {
                $bookId = (int) ($item['book_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);
                $book = $books->get($bookId);

                if ($book === null) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada buku di keranjang yang sudah tidak tersedia.',
                    ]);
                }

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'cart' => 'Jumlah item di keranjang tidak valid.',
                    ]);
                }

                if ((int) $book->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok buku {$book->title} tersisa {$book->stock}. Silakan sesuaikan jumlah pesanan.",
                    ]);
                }

                $price = (float) $book->price;
                $subtotal = $price * $quantity;
                $computedOrderTotal += $subtotal;

                $normalizedCartItems[] = [
                    'book' => $book,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            // Tentukan status berdasarkan metode pembayaran
            $status = self::ORDER_STATUS_WAITING_CONFIRMATION;
            if ($paymentMethod === self::PAYMENT_CASH) {
                $status = self::ORDER_STATUS_WAITING_PAYMENT; // Tunggu admin konfirmasi pembayaran
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'total_price' => $computedOrderTotal,
                'payment_method' => $paymentMethod,
                'midtrans_payment_type' => $paymentMethod === self::PAYMENT_MIDTRANS ? $paymentDetail : null,
                'status' => $status,
                'phone' => $validated['phone'],
                'shipping_address' => $validated['shipping_address'],
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($normalizedCartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['book']->decrement('stock', $item['quantity']);
            }

            return $order;
        });
    }

    private function canRetryMidtransPayment(Order $order): bool
    {
        return in_array((string) $order->midtrans_transaction_status, self::RETRYABLE_MIDTRANS_STATUSES, true)
            || in_array((string) $order->status, self::RETRYABLE_ORDER_STATUSES, true);
    }

    private function abortIfNotOrderOwner(Request $request, Order $order): void
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function syncPaymentResultToOrder(
        Order $order,
        string $midtransOrderId,
        string $transactionStatus,
        string $fraudStatus,
        string $paymentType,
    ): void {
        $order->update([
            'status' => $this->resolveOrderStatusFromPaymentStatus($order, $transactionStatus, $fraudStatus),
            'payment_method' => self::PAYMENT_MIDTRANS,
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_payment_type' => $paymentType,
            'midtrans_fraud_status' => $fraudStatus ?: null,
            'paid_at' => in_array($transactionStatus, ['settlement', 'capture'], true)
                ? ($order->paid_at ?? now())
                : $order->paid_at,
        ]);
    }

    private function orderStatusFilters(): array
    {
        return [
            'all' => null,
            'menunggu_konfirmasi' => [self::ORDER_STATUS_WAITING_CONFIRMATION, self::ORDER_STATUS_WAITING_VERIFICATION],
            'dibayar' => [self::ORDER_STATUS_PAID],
            'diproses' => [self::ORDER_STATUS_PROCESSED],
            'dikirim' => [self::ORDER_STATUS_SHIPPED],
            'selesai' => [self::ORDER_STATUS_DONE],
            'dibatalkan' => [self::ORDER_STATUS_FAILED],
        ];
    }

    private function buildPackageStats(int $userId, array $statusFilters): array
    {
        return collect($statusFilters)
            ->mapWithKeys(function ($statuses, $key) use ($userId): array {
                $query = Order::query()->where('user_id', $userId);

                if (is_array($statuses)) {
                    $query->whereIn('status', $statuses);
                }

                return [$key => $query->count()];
            })
            ->all();
    }
}
