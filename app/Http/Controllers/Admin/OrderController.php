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
    public function index(Request $request): View
    {
        $this->autoFinalizeDueOrders();
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

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        if ($order->status === 'Selesai') {
            return back()->with('error', 'Pesanan sudah selesai dan tidak bisa diubah lagi.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Diproses,Dikirim,Selesai'],
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'Diproses') {
            $updateData['shipped_at'] = null;
            $updateData['estimated_delivery_at'] = null;
        }

        if ($validated['status'] === 'Dikirim') {
            $updateData['shipped_at'] = $order->shipped_at ?? now();
            $updateData['estimated_delivery_at'] = $order->estimated_delivery_at ?? now()->addDays(2);
        }

        if ($validated['status'] === 'Selesai') {
            $updateData['received_at'] = $order->received_at ?? now();
        }

        $order->update($updateData);

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }

    private function autoFinalizeDueOrders(): void
    {
        Order::query()
            ->where('status', 'Dikirim')
            ->where('payment_method', '!=', 'COD')
            ->whereNotNull('estimated_delivery_at')
            ->where('estimated_delivery_at', '<=', now())
            ->update([
                'status' => 'Selesai',
                'received_at' => DB::raw('COALESCE(received_at, CURRENT_TIMESTAMP)'),
            ]);
    }
}
