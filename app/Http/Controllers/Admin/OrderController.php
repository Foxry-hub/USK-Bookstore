<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan list semua pesanan dari user.
     */
    public function index(Request $request): View
    {
        $paymentStatus = (string) $request->query('payment_status', 'all');

        $ordersQuery = Order::with(['user', 'items.book'])->latest();

        if ($paymentStatus !== 'all') {
            if ($paymentStatus === 'cod') {
                $ordersQuery->where('payment_method', 'COD');
            } else {
                $ordersQuery->where('midtrans_transaction_status', $paymentStatus);
            }
        }

        return view('admin.orders.index', [
            'orders' => $ordersQuery->paginate(10)->withQueryString(),
            'paymentStatus' => $paymentStatus,
        ]);
    }

    /**
     * Update status pesanan kalau admin sudah proses order.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Menunggu Pembayaran,Menunggu Konfirmasi,Menunggu Verifikasi,Dibayar,Diproses,Dikirim,Selesai,Pembayaran Gagal,Refund'],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }
}
