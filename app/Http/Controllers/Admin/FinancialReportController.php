<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    private const MIDTRANS_PAID_STATUSES = ['settlement', 'capture'];

    public function index(Request $request): View
    {
        [$fromDate, $toDate, $paymentMethod] = $this->validatedFilters($request);

        $paidOrdersQuery = $this->buildPaidOrdersQuery($fromDate, $toDate, $paymentMethod)
            ->with('user')
            ->latest();

        $totalRevenue = (float) (clone $paidOrdersQuery)->sum('total_price');
        $totalPaidOrders = (int) (clone $paidOrdersQuery)->count();
        $averageOrderValue = $totalPaidOrders > 0 ? $totalRevenue / $totalPaidOrders : 0;
        $codRevenue = (float) (clone $paidOrdersQuery)->where('payment_method', 'COD')->sum('total_price');
        $midtransRevenue = (float) (clone $paidOrdersQuery)->where('payment_method', 'MIDTRANS')->sum('total_price');

        return view('admin.reports.financial', [
            'orders' => $paidOrdersQuery->paginate(15)->withQueryString(),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'paymentMethod' => $paymentMethod,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_paid_orders' => $totalPaidOrders,
                'average_order_value' => $averageOrderValue,
                'cod_revenue' => $codRevenue,
                'midtrans_revenue' => $midtransRevenue,
            ],
        ]);
    }

    public function downloadPdf(Request $request)
    {
        [$fromDate, $toDate, $paymentMethod] = $this->validatedFilters($request);

        $orders = $this->buildPaidOrdersQuery($fromDate, $toDate, $paymentMethod)
            ->with('user')
            ->latest()
            ->get();

        $summary = [
            'total_revenue' => (float) $orders->sum('total_price'),
            'total_paid_orders' => $orders->count(),
            'average_order_value' => $orders->count() > 0 ? ((float) $orders->sum('total_price') / $orders->count()) : 0,
            'cod_revenue' => (float) $orders->where('payment_method', 'COD')->sum('total_price'),
            'midtrans_revenue' => (float) $orders->where('payment_method', 'MIDTRANS')->sum('total_price'),
        ];

        $pdf = Pdf::loadView('admin.reports.financial-pdf', [
            'orders' => $orders,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'paymentMethod' => $paymentMethod,
            'summary' => $summary,
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-keuangan-' . $fromDate . '-sampai-' . $toDate . '.pdf');
    }

    private function buildPaidOrdersQuery(string $fromDate, string $toDate, string $paymentMethod): Builder
    {
        $fromDateTime = $fromDate . ' 00:00:00';
        $toDateTime = $toDate . ' 23:59:59';

        $query = Order::query()
            ->whereBetween('created_at', [$fromDateTime, $toDateTime])
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNotNull('paid_at')
                    ->orWhereIn('midtrans_transaction_status', self::MIDTRANS_PAID_STATUSES)
                    ->orWhere(function (Builder $codBuilder): void {
                        $codBuilder
                            ->where('payment_method', 'COD')
                            ->whereIn('status', ['Diproses', 'Dikirim', 'Selesai']);
                    });
            });

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        return $query;
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'payment_method' => ['nullable', 'in:all,COD,MIDTRANS'],
        ]);

        $fromDate = (string) ($validated['from'] ?? now()->startOfMonth()->toDateString());
        $toDate = (string) ($validated['to'] ?? now()->toDateString());
        $paymentMethod = (string) ($validated['payment_method'] ?? 'all');

        return [$fromDate, $toDate, $paymentMethod];
    }
}
