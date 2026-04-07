@extends('layouts.app')

@section('content')
@php
    $subTotal = $total;
    $shippingFee = 0;
    $grandTotal = $subTotal + $shippingFee;
    $currentUser = auth()->user();
@endphp

<style>
    .pay-option {
        transition: all 200ms ease;
    }

    .pay-option-active {
        border-color: rgb(59 130 246);
        background: linear-gradient(135deg, rgba(239, 246, 255, 0.95), rgba(255, 255, 255, 1));
        box-shadow: 0 10px 24px -18px rgba(37, 99, 235, 0.9);
        transform: translateY(-1px);
    }

    .pay-option-dot {
        transition: all 180ms ease;
    }

    .pay-icon {
        transition: all 200ms ease;
    }

    .pay-option-active .pay-option-dot {
        border-color: rgb(37 99 235);
        background-color: rgb(37 99 235);
        box-shadow: inset 0 0 0 3px #fff;
    }

    .pay-option-active .pay-icon {
        background: rgb(37 99 235);
        color: #fff;
    }

    .cart-summary-sticky {
        transition: top 220ms ease, box-shadow 220ms ease;
    }

    @media (min-width: 1280px) {
        .cart-summary-sticky {
            top: 6.5rem;
        }
    }
</style>

<section class="space-y-6">
    <div class="rounded-3xl border border-slate-200 bg-white px-5 py-5 shadow-sm sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Checkout</p>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900 sm:text-3xl">Payment</h1>
            </div>
            <a href="{{ route('store.catalog') }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-800 transition hover:border-slate-900 hover:bg-slate-900 hover:text-white">Kembali belanja</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(340px,1fr)]">
        <div class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Delivery address</p>
                        <p class="mt-1 text-sm text-slate-600">Isi alamat dan nomor aktif untuk pengiriman.</p>
                    </div>
                </div>

                @if (blank($currentUser?->phone) || blank($currentUser?->address))
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Profile kamu belum lengkap. Lengkapi dulu nomor telepon dan alamat di halaman Profile supaya checkout lebih cepat.
                        <a href="{{ route('profile.edit') }}" class="ml-1 font-semibold underline">Buka Profile</a>
                    </div>
                @endif

                <form action="{{ route('checkout.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Nomor HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $currentUser?->phone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100" required>
                        @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Alamat Pengiriman</label>
                        <textarea name="shipping_address" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100" required>{{ old('shipping_address', $currentUser?->address) }}</textarea>
                        @error('shipping_address') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan (Opsional)</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-100">{{ old('note') }}</textarea>
                    </div>

                    <div>
                        @php
                            $oldMethod = old('payment_method', 'MIDTRANS');
                            $oldDetail = old('payment_detail', $oldMethod === 'COD' ? 'cod' : 'card');
                        @endphp
                        <input type="hidden" name="payment_method" id="payment-method-input" value="{{ $oldMethod }}">

                        <p class="mb-3 text-sm font-semibold text-slate-800">Payment details</p>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="pay-option group flex h-full min-w-0 cursor-pointer rounded-2xl border border-slate-300 bg-white px-4 py-3 hover:border-brand-400" data-pay-option>
                                <input type="radio" name="payment_detail" value="card" class="sr-only" @checked($oldDetail === 'card')>
                                <div class="flex w-full items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="pay-icon mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18v9H3z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5h18" />
                                            </svg>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-slate-800">Debit/Credit Card</span>
                                            <span class="mt-1 block text-xs leading-snug text-slate-500">Bayar pakai kartu debit atau credit.</span>
                                        </span>
                                    </div>
                                    <span class="pay-option-dot mt-1 inline-flex h-5 w-5 rounded-full border border-slate-300"></span>
                                </div>
                            </label>

                            <label class="pay-option group flex h-full min-w-0 cursor-pointer rounded-2xl border border-slate-300 bg-white px-4 py-3 hover:border-brand-400" data-pay-option>
                                <input type="radio" name="payment_detail" value="cod" class="sr-only" @checked($oldDetail === 'cod')>
                                <div class="flex w-full items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-start gap-3">
                                        <span class="pay-icon mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18v9H3z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5h18" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 15h3" />
                                            </svg>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-semibold text-slate-800">Cash On Delivery (COD)</span>
                                            <span class="mt-1 block text-xs leading-snug text-slate-500">Barang datang dulu, baru bayar di tempat.</span>
                                        </span>
                                    </div>
                                    <span class="pay-option-dot mt-1 inline-flex h-5 w-5 rounded-full border border-slate-300"></span>
                                </div>
                            </label>
                        </div>
                        @error('payment_method') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-brand-600">Pay Rp {{ number_format($grandTotal, 0, ',', '.') }}</button>
                </form>
            </div>

        </div>

        <aside class="cart-summary-sticky h-fit space-y-4 xl:sticky">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Item Pesanan</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Barang yang kamu pilih</h2>
                    </div>
                </div>

                <div id="cart-items" class="space-y-3">
                @forelse ($cart as $item)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-3 shadow-sm" data-cart-item="{{ $item['book_id'] }}">
                        <div class="flex items-start gap-3">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                <img src="{{ $item['image_url'] ?: 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=500&q=80' }}" alt="{{ $item['title'] }}" class="h-20 w-14 rounded-lg object-cover">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold leading-snug text-slate-900">{{ $item['title'] }}</h3>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                                    <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="decrease" data-book-id="{{ $item['book_id'] }}">-</button>
                                    <span class="min-w-10 px-3 text-center text-sm font-semibold text-slate-900" data-cart-quantity="{{ $item['book_id'] }}">{{ $item['quantity'] }}</span>
                                    <button type="button" class="cart-stepper flex h-9 w-9 items-center justify-center rounded-lg text-base font-bold text-slate-700 hover:bg-white" data-cart-action="increase" data-book-id="{{ $item['book_id'] }}">+</button>
                                </div>

                                <form action="{{ route('cart.remove', $item['book_id']) }}" method="POST" class="cart-remove-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-rose-200 text-rose-600 transition hover:bg-rose-50" aria-label="Hapus item" title="Hapus item">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l.75 12.5A1.75 1.75 0 0 0 8.5 20h7a1.75 1.75 0 0 0 1.75-1.5L18 6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 10v5M14 10v5" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div id="cart-empty" class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-500">
                        Keranjang kamu masih kosong nih.
                    </div>
                @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-xl font-bold text-slate-900">Order summary</h2>
                <p class="mt-1 text-sm text-slate-500">Checkout aman dengan Midtrans Sandbox.</p>

                <div class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($subTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Shipping</span>
                        <span class="font-semibold text-emerald-600">Gratis</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                    <span class="text-sm font-semibold text-slate-700">Total</span>
                    <span id="cart-total" class="text-2xl font-extrabold text-slate-900">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>

                <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Metode Midtrans</p>
                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                        <li>Debit/Credit Card</li>
                        <li>COD</li>
                    </ul>
                </div>
            </section>
        </aside>
    </div>
</section>

<template id="cart-empty-template">
    <div id="cart-empty" class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-500">
        Keranjang kamu masih kosong nih.
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const paymentRadios = document.querySelectorAll('input[name="payment_detail"]');
        const paymentMethodInput = document.getElementById('payment-method-input');

        if (paymentRadios.length === 0 || !paymentMethodInput) {
            return;
        }

        const mapDetailToMethod = (detail) => detail === 'cod' ? 'COD' : 'MIDTRANS';

        const syncPaymentOptionState = () => {
            paymentRadios.forEach((radio) => {
                const option = radio.closest('[data-pay-option]');

                if (!option) {
                    return;
                }

                option.classList.toggle('pay-option-active', radio.checked);

                if (radio.checked) {
                    paymentMethodInput.value = mapDetailToMethod(radio.value);
                }
            });
        };

        paymentRadios.forEach((radio) => {
            radio.addEventListener('change', syncPaymentOptionState);
        });

        syncPaymentOptionState();
    });
</script>

@endsection
