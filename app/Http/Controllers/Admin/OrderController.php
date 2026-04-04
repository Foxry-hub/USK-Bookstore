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
    public function index(): View
    {
        return view('admin.orders.index', [
            'orders' => Order::with(['user', 'items.book'])->latest()->paginate(10),
        ]);
    }

    /**
     * Update status pesanan kalau admin sudah proses order.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Menunggu Konfirmasi,Diproses,Dikirim,Selesai'],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status pesanan berhasil diupdate.');
    }
}
