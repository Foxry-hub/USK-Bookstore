<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::controller(StorefrontController::class)->group(function (): void {
    Route::get('/', 'index')->name('store.index');
    Route::get('/catalog', 'catalog')->name('store.catalog');
    Route::get('/books/{book}', 'show')->name('store.show');
});

Route::post('/contact-messages', [ContactMessageController::class, 'store'])->name('contact.store');
Route::post('/payments/midtrans/notification', [CheckoutController::class, 'notification'])->name('payments.midtrans.notification');

Route::middleware('guest')->group(function (): void {
    Route::controller(AuthController::class)->group(function (): void {
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register')->name('register.store');
        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login')->name('login.store');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('cart')->name('cart.')->controller(CartController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/{book}', 'add')->name('add');
        Route::patch('/{bookId}', 'update')->name('update');
        Route::delete('/{bookId}', 'remove')->name('remove');
    });

    Route::prefix('orders')->name('orders.')->controller(CheckoutController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/{order}/pay', 'pay')->name('pay');
        Route::post('/{order}/confirm-received', 'confirmReceived')->name('confirm-received');
        Route::post('/{order}/confirm-not-received', 'confirmNotReceived')->name('confirm-not-received');
        Route::get('/{order}/invoice', 'downloadInvoice')->name('invoice.download');
    });

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function (): void {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/landing-preview', [StorefrontController::class, 'index'])->name('landing-preview');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('books', AdminBookController::class)->except(['show']);

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('/messages', [AdminContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [AdminContactMessageController::class, 'show'])->name('messages.show');
});
