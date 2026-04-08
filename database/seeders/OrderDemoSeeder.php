<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::updateOrCreate(
            ['email' => 'customer@bookstore.test'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('customer123'),
                'is_admin' => false,
                'phone' => '081300000001',
                'address' => 'Jl. Demo Customer No. 1, Jakarta',
            ]
        );

        $books = Book::query()->orderBy('id')->take(6)->get();

        if ($books->count() < 3) {
            return;
        }

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-COD-DUE',
            paymentMethod: 'COD',
            status: 'Dikirim',
            createdAt: now()->subDays(5),
            estimatedDeliveryAt: now()->subHours(3),
            items: [
                ['book' => $books[0], 'quantity' => 1],
                ['book' => $books[0], 'quantity' => 2],
                ['book' => $books[1], 'quantity' => 1],
            ]
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-COD-UPCOMING',
            paymentMethod: 'COD',
            status: 'Dikirim',
            createdAt: now()->subDays(2),
            estimatedDeliveryAt: now()->addHours(20),
            items: [
                ['book' => $books[2], 'quantity' => 1],
                ['book' => $books[3], 'quantity' => 1],
            ]
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-MIDTRANS-PAID',
            paymentMethod: 'MIDTRANS',
            status: 'Dibayar',
            createdAt: now()->subDays(1),
            estimatedDeliveryAt: null,
            items: [
                ['book' => $books[4], 'quantity' => 1],
                ['book' => $books[5], 'quantity' => 1],
            ],
            midtransStatus: 'capture'
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-COD-DONE',
            paymentMethod: 'COD',
            status: 'Selesai',
            createdAt: now()->subDays(8),
            estimatedDeliveryAt: now()->subDays(6),
            receivedAt: now()->subDays(6),
            items: [
                ['book' => $books[1], 'quantity' => 1],
                ['book' => $books[2], 'quantity' => 2],
            ]
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-GROUPING-CLEAR',
            paymentMethod: 'COD',
            status: 'Diproses',
            createdAt: now()->subDays(3),
            estimatedDeliveryAt: now()->addDays(2),
            items: [
                ['book' => $books[0], 'quantity' => 2],
                ['book' => $books[0], 'quantity' => 3],
                ['book' => $books[0], 'quantity' => 1],
                ['book' => $books[1], 'quantity' => 1],
            ]
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-MULTIPLE-COPIES',
            paymentMethod: 'MIDTRANS',
            status: 'Diproses',
            createdAt: now()->subDays(1),
            estimatedDeliveryAt: null,
            items: [
                ['book' => $books[3], 'quantity' => 2],
                ['book' => $books[4], 'quantity' => 1],
                ['book' => $books[3], 'quantity' => 3],
                ['book' => $books[4], 'quantity' => 2],
                ['book' => $books[3], 'quantity' => 1],
            ],
            midtransStatus: 'capture'
        );

        $this->seedOrder(
            user: $customer,
            orderCode: 'ORD-DEMO-COD-NOT-RECEIVED',
            paymentMethod: 'COD',
            status: 'Pembayaran Gagal',
            createdAt: now()->subHours(18),
            estimatedDeliveryAt: now()->subHours(3),
            receivedAt: now()->subHours(2),
            items: [
                ['book' => $books[2], 'quantity' => 1],
                ['book' => $books[2], 'quantity' => 2],
                ['book' => $books[5], 'quantity' => 1],
            ]
        );
    }

    private function seedOrder(
        User $user,
        string $orderCode,
        string $paymentMethod,
        string $status,
        $createdAt,
        $estimatedDeliveryAt,
        array $items,
        ?string $midtransStatus = null,
        $receivedAt = null
    ): void {
        $order = Order::updateOrCreate(
            ['order_code' => $orderCode],
            [
                'user_id' => $user->id,
                'total_price' => 0,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'phone' => $user->phone ?? '081300000001',
                'shipping_address' => $user->address ?? 'Alamat demo',
                'note' => 'Seeder demo order',
                'midtrans_order_id' => $paymentMethod === 'MIDTRANS' ? $orderCode . '-MT' : null,
                'midtrans_transaction_status' => $midtransStatus,
                'estimated_delivery_at' => $estimatedDeliveryAt,
                'received_at' => $receivedAt,
                'paid_at' => in_array($status, ['Dibayar', 'Diproses', 'Dikirim', 'Selesai'], true) ? ($createdAt->copy()->addHours(2)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]
        );

        OrderItem::query()->where('order_id', $order->id)->delete();

        $total = 0;

        foreach ($items as $itemData) {
            /** @var \App\Models\Book $book */
            $book = $itemData['book'];
            $quantity = (int) $itemData['quantity'];
            $price = (float) $book->price;
            $subtotal = $price * $quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'book_id' => $book->id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
            ]);

            $total += $subtotal;
        }

        $order->update([
            'total_price' => $total,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
