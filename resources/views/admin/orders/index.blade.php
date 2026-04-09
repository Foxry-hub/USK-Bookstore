@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-2xl font-bold text-slate-900">List Pesanan User</h1>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-3">
            <div>
                <label for="payment_status" class="mb-1 block text-xs font-semibold text-slate-700">Filter Status Pembayaran</label>
                <select id="payment_status" name="payment_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="all" @selected(($paymentStatus ?? 'all') === 'all')>Semua</option>
                    <option value="cod" @selected(($paymentStatus ?? 'all') === 'cod')>COD</option>
                    <option value="pending" @selected(($paymentStatus ?? 'all') === 'pending')>Midtrans Pending</option>
                    <option value="settlement" @selected(($paymentStatus ?? 'all') === 'settlement')>Midtrans Settlement</option>
                    <option value="capture" @selected(($paymentStatus ?? 'all') === 'capture')>Midtrans Capture</option>
                    <option value="deny" @selected(($paymentStatus ?? 'all') === 'deny')>Midtrans Deny</option>
                    <option value="cancel" @selected(($paymentStatus ?? 'all') === 'cancel')>Midtrans Cancel</option>
                    <option value="expire" @selected(($paymentStatus ?? 'all') === 'expire')>Midtrans Expire</option>
                    <option value="refund" @selected(($paymentStatus ?? 'all') === 'refund')>Midtrans Refund</option>
                    <option value="partial_refund" @selected(($paymentStatus ?? 'all') === 'partial_refund')>Midtrans Partial Refund</option>
                </select>
            </div>

            <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Terapkan Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Reset</a>
        </form>

        <div class="mt-4 space-y-4">
            @forelse ($orders as $order)
                @php
                    $statusClasses = [
                        'Menunggu Konfirmasi' => 'bg-blue-100 text-blue-800',
                        'Menunggu Verifikasi' => 'bg-indigo-100 text-indigo-800',
                        'Dibayar' => 'bg-emerald-100 text-emerald-800',
                        'Diproses' => 'bg-cyan-100 text-cyan-800',
                        'Dikirim' => 'bg-sky-100 text-sky-800',
                        'Selesai' => 'bg-green-100 text-green-800',
                        'Pembayaran Gagal' => 'bg-rose-100 text-rose-800',
                        'Refund' => 'bg-slate-200 text-slate-700',
                    ];

                    $paymentStatusClasses = [
                        'pending' => 'bg-amber-100 text-amber-800',
                        'settlement' => 'bg-emerald-100 text-emerald-800',
                        'capture' => 'bg-emerald-100 text-emerald-800',
                        'deny' => 'bg-rose-100 text-rose-800',
                        'cancel' => 'bg-rose-100 text-rose-800',
                        'expire' => 'bg-rose-100 text-rose-800',
                        'refund' => 'bg-slate-200 text-slate-700',
                        'partial_refund' => 'bg-slate-200 text-slate-700',
                    ];
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                            <p class="text-sm text-slate-600">Pemesan: {{ $order->user->name }} ({{ $order->user->email }})</p>
                            <p class="text-sm text-slate-600">Total: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            <div class="mt-1 text-sm text-slate-600">
                                <span>Status Order:</span>
                                <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->status }}</span>
                            </div>
                            @if ($order->payment_method === 'COD')
                                <div class="mt-1 text-sm text-slate-600">
                                    <span>Status Pembayaran:</span>
                                    <span class="ml-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">COD</span>
                                </div>
                            @elseif ($order->payment_method === 'CASH')
                                <div class="mt-1 text-sm text-slate-600">
                                    <span>Status Pembayaran:</span>
                                    @if ($order->status === 'Menunggu Pembayaran')
                                        <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Menunggu Pembayaran Tunai</span>
                                    @elseif ($order->cash_payment_confirmed_at)
                                        <span class="ml-1 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Pembayaran Tunai Dikonfirmasi</span>
                                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs">
                                            <p><span class="font-semibold text-slate-700">Uang Diterima:</span> Rp {{ number_format($order->cash_amount_paid, 0, ',', '.') }}</p>
                                            <p><span class="font-semibold text-slate-700">Kembalian:</span> Rp {{ number_format($order->cash_change, 0, ',', '.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($order->midtrans_transaction_status)
                                <div class="mt-1 text-sm text-slate-600">
                                    <span>Status Pembayaran:</span>
                                    <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $paymentStatusClasses[$order->midtrans_transaction_status] ?? 'bg-slate-100 text-slate-700' }}">{{ $order->midtrans_transaction_status }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if ($order->payment_method === 'CASH' && $order->status === 'Menunggu Pembayaran')
                                <button type="button" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700" onclick="openCashPaymentModal('{{ $order->id }}', {{ $order->total_price }})">Konfirmasi Pembayaran Tunai</button>
                            @elseif ($order->status === 'Menunggu Konfirmasi' || $order->status === 'Dibayar' || $order->status === 'Pembayaran Gagal')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Diproses">
                                    <button type="submit" class="rounded-lg bg-cyan-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-cyan-700">Diproses</button>
                                </form>
                            @endif

                            @if ($order->status === 'Diproses')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Dikirim">
                                    <button type="submit" class="rounded-lg bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700">Dikirim</button>
                                </form>
                            @endif

                            @if ($order->status === 'Dikirim')
                                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="Selesai">
                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Tandai Selesai</button>
                                </form>
                            @endif

                            @if ($order->status === 'Selesai')
                                <span class="inline-flex cursor-default rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Selesai</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-3">
                        <p>Shipped at: <span class="font-semibold text-slate-900">{{ $order->shipped_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                        <p>Estimasi sampai: <span class="font-semibold text-slate-900">{{ $order->estimated_delivery_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                        <p>Selesai: <span class="font-semibold text-slate-900">{{ $order->received_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-50 p-3">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between py-1 text-sm text-slate-700">
                                <span>{{ $item->book->title }} x {{ $item->quantity }}</span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500">
                    Belum ada pesanan masuk.
                </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    </section>

    <!-- Modal POS Pembayaran Tunai dengan Preview Pesanan -->
    <div id="cash-payment-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl">
                <button type="button" class="absolute right-4 top-4 z-10 text-slate-400 hover:text-slate-900" onclick="closeCashPaymentModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="grid gap-6 p-6 sm:grid-cols-2">
                    <!-- Left Side: Order & Transaction Details -->
                    <div class="space-y-4">
                        <!-- Header -->
                        <div class="border-b border-slate-200 pb-4">
                            <h2 class="mb-3 text-2xl font-bold text-slate-900">Payment</h2>
                            
                            <!-- Customer Info -->
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 uppercase">Customer</p>
                                    <h3 id="customer-name" class="text-lg font-bold text-slate-900">-</h3>
                                    <p id="customer-email" class="text-sm text-slate-600">-</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase">Order Code</p>
                                        <p id="order-code" class="font-semibold text-slate-900">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-500 uppercase">Date</p>
                                        <p id="order-date" class="font-semibold text-slate-900">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div>
                            <p class="mb-3 text-sm font-semibold text-slate-700 uppercase">Transaction Details</p>
                            <div id="transaction-items" class="space-y-2">
                                <!-- Items akan dimuat di sini -->
                            </div>
                            <div class="mt-3 border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-slate-700">Total</p>
                                    <p id="total-display" class="text-xl font-bold text-slate-900">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Payment Input (POS System) -->
                    <div class="flex flex-col gap-4">
                        <p class="text-sm font-semibold text-slate-700 uppercase">Select a payment method</p>
                        
                        <!-- Amount Display -->
                        <div class="space-y-2">
                            <p class="text-xs font-semibold text-slate-500">Amount</p>
                            <div class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white p-4">
                                <span class="text-4xl font-bold text-slate-900">Rp</span>
                                <span id="cash-display" class="flex-1 text-right text-4xl font-bold text-slate-900">0</span>
                            </div>
                        </div>

                        <!-- Numeric Keypad -->
                        <div class="flex-1">
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="1">1</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="2">2</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="3">3</button>
                                
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="4">4</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="5">5</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="6">6</button>
                                
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="7">7</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="8">8</button>
                                <button type="button" class="keypad-btn rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" data-value="9">9</button>
                                
                                <button type="button" class="col-span-2 rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" id="keypad-0">0</button>
                                <button type="button" class="rounded-lg border border-slate-300 bg-white py-4 text-xl font-bold text-slate-900 transition hover:bg-slate-100 active:bg-slate-200" id="keypad-clear">C</button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <form id="cash-payment-form" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="cash_amount_paid" id="cash-amount-paid">
                            
                            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-lg font-bold text-white transition hover:bg-emerald-700 active:bg-emerald-800" id="pay-btn">Pay Now</button>
                            <button type="button" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 transition hover:bg-slate-50" onclick="closeCashPaymentModal()">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentOrderData = null;
        let currentAmount = '';

        function openCashPaymentModal(orderId, totalAmount) {
            const modal = document.getElementById('cash-payment-modal');
            const form = document.getElementById('cash-payment-form');
            
            // Set form action
            form.action = `/admin/orders/${orderId}/confirm-cash-payment`;
            
            // Reset display
            currentAmount = '';
            updateCashDisplay();
            
            // Fetch order data
            fetch(`{{ route('admin.orders.index') }}`.replace(/\/admin\/orders.*/, `/admin/orders/${orderId}/preview`))
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Gagal memuat data pesanan');
                        return;
                    }

                    currentOrderData = data.order;
                    renderOrderPreview(data.order);
                    modal.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data pesanan');
                });
        }

        function closeCashPaymentModal() {
            document.getElementById('cash-payment-modal').classList.add('hidden');
            currentOrderData = null;
            currentAmount = '';
        }

        function renderOrderPreview(order) {
            // Customer Info
            document.getElementById('customer-name').textContent = order.user.name;
            document.getElementById('customer-email').textContent = order.user.email;
            document.getElementById('order-code').textContent = order.order_code;
            document.getElementById('order-date').textContent = new Date(order.created_at).toLocaleDateString('id-ID', {
                weekday: 'short',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Transaction Items
            const itemsContainer = document.getElementById('transaction-items');
            itemsContainer.innerHTML = order.items.map(item => `
                <div class="flex items-start justify-between border-b border-slate-100 py-2 text-sm">
                    <div>
                        <p class="font-semibold text-slate-900">${item.book.title}</p>
                        <p class="text-xs text-slate-500">Rp ${parseInt(item.price).toLocaleString('id-ID')} x ${item.quantity}</p>
                    </div>
                    <p class="font-bold text-slate-900">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</p>
                </div>
            `).join('');

            // Total
            document.getElementById('total-display').textContent = 'Rp ' + parseInt(order.total_price).toLocaleString('id-ID');
            document.getElementById('cash-amount-paid').dataset.total = order.total_price;
        }

        // Keypad functionality
        document.querySelectorAll('.keypad-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                currentAmount += this.dataset.value;
                updateCashDisplay();
            });
        });

        document.getElementById('keypad-0').addEventListener('click', function() {
            currentAmount += '0';
            updateCashDisplay();
        });

        document.getElementById('keypad-clear').addEventListener('click', function() {
            currentAmount = '';
            updateCashDisplay();
        });

        function updateCashDisplay() {
            const amount = currentAmount ? parseInt(currentAmount) : 0;
            document.getElementById('cash-display').textContent = amount.toLocaleString('id-ID');
            document.getElementById('cash-amount-paid').value = amount;

            // Update button state
            const payBtn = document.getElementById('pay-btn');
            const total = parseFloat(document.getElementById('cash-amount-paid').dataset.total) || 0;
            
            if (amount < total) {
                payBtn.disabled = true;
                payBtn.classList.add('opacity-50', 'cursor-not-allowed');
                payBtn.classList.remove('hover:bg-emerald-700');
            } else {
                payBtn.disabled = false;
                payBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                payBtn.classList.add('hover:bg-emerald-700');
            }
        }

        // Form submission
        document.getElementById('cash-payment-form').addEventListener('submit', function(e) {
            const total = parseFloat(document.getElementById('cash-amount-paid').dataset.total) || 0;
            const amount = parseInt(document.getElementById('cash-amount-paid').value) || 0;

            if (amount < total) {
                e.preventDefault();
                alert('Jumlah uang tidak boleh kurang dari total tagihan!');
            }
        });

        // Close modal when clicking outside
        document.getElementById('cash-payment-modal').addEventListener('click', function(e) {
            if (e.target === this) closeCashPaymentModal();
        });
    </script>
@endsection
