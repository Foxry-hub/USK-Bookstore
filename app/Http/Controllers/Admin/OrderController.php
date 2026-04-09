<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const ORDER_STATUS_PROCESSED = 'Diproses';

    private const ORDER_STATUS_SHIPPED = 'Dikirim';

    private const ORDER_STATUS_DONE = 'Selesai';

    private const ORDER_STATUS_PAID = 'Dibayar';

    private const PAYMENT_COD = 'COD';

    private const PAYMENT_CASH = 'CASH';

    public function index(Request $request): View
    {
        $this->autoFinalizeDueOrders();
        $paymentStatus = (string) $request->query('payment_status', 'all');

        $ordersQuery = Order::with(['user', 'items.book'])->latest();

        if ($paymentStatus !== 'all') {
            $ordersQuery = $this->filterOrdersByPaymentStatus($ordersQuery, $paymentStatus);
        }

        return view('admin.orders.index', [
            'orders' => $ordersQuery->paginate(10)->withQueryString(),
            'paymentStatus' => $paymentStatus,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        // Order yang udah selesai jangan dirubah statusnya, tuh udah final.
        if ($order->status === self::ORDER_STATUS_DONE) {
            return back()->with('error', 'Pesanan sudah selesai dan tidak bisa diubah lagi.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Diproses,Dikirim,Selesai'],
        ]);

        $updateData = $this->buildStatusUpdateData($order, $validated['status']);
        $order->update($updateData);

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }

    public function confirmCashPayment(Request $request, Order $order): RedirectResponse
    {
        // Hanya bisa process cash payment untuk CASH payment method
        if ($order->payment_method !== self::PAYMENT_CASH) {
            return back()->with('error', 'Pesanan ini bukan metode pembayaran tunai.');
        }

        // Hanya bisa confirm jika status masih menunggu pembayaran
        if ($order->status !== 'Menunggu Pembayaran') {
            return back()->with('error', 'Pesanan sudah tidak dalam status menunggu pembayaran.');
        }

        $validated = $request->validate([
            'cash_amount_paid' => ['required', 'numeric', 'min:' . $order->total_price],
        ]);

        $amountPaid = (float) $validated['cash_amount_paid'];
        $change = $amountPaid - (float) $order->total_price;

        $order->update([
            'cash_amount_paid' => $amountPaid,
            'cash_change' => $change,
            'cash_payment_confirmed_at' => now(),
            'status' => self::ORDER_STATUS_DONE,
            'paid_at' => now(),
            'received_at' => now(),
        ]);

        return back()->with('success', "Pembayaran tunai dikonfirmasi. Kembalian: Rp " . number_format($change, 0, ',', '.'));
    }

    public function preview(Order $order)
    {
        $order->load(['user', 'items.book']);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'total_price' => $order->total_price,
                'status' => $order->status,
                'shipping_address' => $order->shipping_address,
                'note' => $order->note,
                'user' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'phone' => $order->phone,
                ],
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'book' => [
                        'id' => $item->book->id,
                        'title' => $item->book->title,
                    ],
                ]),
            ],
        ]);
    }

    private function filterOrdersByPaymentStatus($query, string $paymentStatus)
    {
        if ($paymentStatus === 'cod') {
            return $query->where('payment_method', self::PAYMENT_COD);
        }

        return $query->where('midtrans_transaction_status', $paymentStatus);
    }

    private function buildStatusUpdateData(Order $order, string $newStatus): array
    {
        $data = ['status' => $newStatus];

        if ($newStatus === self::ORDER_STATUS_PROCESSED) {
            // Pas balik ke diproses, hapus tanggal kirim biar nanti bisa di-setup lagi.
            $data['shipped_at'] = null;
            $data['estimated_delivery_at'] = null;
        }

        if ($newStatus === self::ORDER_STATUS_SHIPPED) {
            // Set tanggal kirim dan estimasi diterima (default 2 hari ketika dikasih status dikirim).
            $data['shipped_at'] = $order->shipped_at ?? now();
            $data['estimated_delivery_at'] = $order->estimated_delivery_at ?? now()->addDays(2);
        }

        if ($newStatus === self::ORDER_STATUS_DONE) {
            // Tandai sebagai diterima waktu selesai.
            $data['received_at'] = $order->received_at ?? now();
        }

        return $data;
    }

    private function autoFinalizeDueOrders(): void
    {
        // Auto finalkan order di-kirim yang udah melewati estimasi sampai tapi belum dikonfirmasi user.
        Order::query()
            ->where('status', self::ORDER_STATUS_SHIPPED)
            ->where('payment_method', '!=', self::PAYMENT_COD)
            ->whereNotNull('estimated_delivery_at')
            ->where('estimated_delivery_at', '<=', now())
            ->update([
                'status' => self::ORDER_STATUS_DONE,
                'received_at' => DB::raw('COALESCE(received_at, CURRENT_TIMESTAMP)'),
            ]);
    }
}
