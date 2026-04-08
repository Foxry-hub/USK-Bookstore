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

    private const PAYMENT_COD = 'COD';

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
