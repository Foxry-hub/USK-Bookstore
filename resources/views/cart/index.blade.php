@extends('layouts.app')

@section('content')
<section class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-4">
        <h1 class="text-3xl font-bold text-slate-900">Keranjang Belanja</h1>

        <div id="cart-items" class="space-y-4">
            @forelse ($cart as $item)
                <article
                    class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
                    data-cart-item="{{ $item['book_id'] }}"
                >
                    <div class="flex items-center gap-4">
                        <img src="{{ $item['image_url'] ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $item['title'] }}" class="h-20 w-16 rounded-lg object-cover">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $item['title'] }}</h3>
                            <p class="text-sm text-slate-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="decrease" data-book-id="{{ $item['book_id'] }}">-</button>

                            <span class="min-w-10 px-3 text-center text-sm font-semibold text-slate-900" data-cart-quantity="{{ $item['book_id'] }}">{{ $item['quantity'] }}</span>

                            <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="increase" data-book-id="{{ $item['book_id'] }}">+</button>
                        </div>

                        <form action="{{ route('cart.remove', $item['book_id']) }}" method="POST" class="cart-remove-form">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div id="cart-empty" class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-500">
                    Keranjang kamu masih kosong nih.
                </div>
            @endforelse
        </div>
    </div>

    <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-bold text-slate-900">Checkout</h2>
        <p class="mt-1 text-sm text-slate-600">Pembayaran dilakukan saat barang sampai ke alamatmu.</p>

        <div class="mt-4 flex items-center justify-between text-sm">
            <span>Total</span>
            <span id="cart-total" class="text-lg font-bold text-slate-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        <form action="{{ route('checkout.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor HP</label>
                <input type="text" name="phone" class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm" required>
                @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat Pengiriman</label>
                <textarea name="shipping_address" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm" required></textarea>
                @error('shipping_address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan (Opsional)</label>
                <textarea name="note" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm"></textarea>
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-semibold text-white">Checkout Payment at Delivery</button>
        </form>
    </aside>
</section>

<template id="cart-empty-template">
    <div id="cart-empty" class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-500">
        Keranjang kamu masih kosong nih.
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');
        const emptyTemplate = document.getElementById('cart-empty-template');

        const formatCurrency = (value) => new Intl.NumberFormat('id-ID').format(value);

        const showEmptyState = () => {
            if (document.getElementById('cart-empty')) {
                return;
            }

            cartItems.innerHTML = '';
            cartItems.appendChild(emptyTemplate.content.cloneNode(true));
        };

        const updateSummary = (payload) => {
            if (cartTotal) {
                cartTotal.textContent = `Rp ${formatCurrency(payload.total)}`;
            }

            if (payload.removed) {
                const item = document.querySelector(`[data-cart-item="${payload.book_id}"]`);
                if (item) {
                    item.remove();
                }

                if (!cartItems.querySelector('[data-cart-item]')) {
                    showEmptyState();
                }
                return;
            }

            const quantityBadge = document.querySelector(`[data-cart-quantity="${payload.book_id}"]`);
            if (quantityBadge && payload.quantity !== null) {
                quantityBadge.textContent = payload.quantity;
            }
        };

        const sendUpdate = async (bookId, quantity) => {
            const response = await fetch(`{{ url('/cart') }}/${bookId}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ quantity }),
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            updateSummary(payload);
        };

        document.querySelectorAll('[data-cart-action]').forEach((button) => {
            button.addEventListener('click', async () => {
                const bookId = button.dataset.bookId;
                const action = button.dataset.cartAction;
                const quantityBadge = document.querySelector(`[data-cart-quantity="${bookId}"]`);
                const currentQuantity = Number(quantityBadge?.textContent || 1);

                if (action === 'decrease') {
                    await sendUpdate(bookId, currentQuantity - 1);
                    return;
                }

                await sendUpdate(bookId, currentQuantity + 1);
            });
        });

        document.querySelectorAll('.cart-remove-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams(formData),
                });

                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                updateSummary(payload);
            });
        });
    });
</script>
@endsection
