<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bikin tabel pesanan untuk nyimpen checkout COD user.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_code')->unique();
            $table->decimal('total_price', 12, 2);
            $table->string('payment_method')->default('COD');
            $table->string('status')->default('Menunggu Konfirmasi');
            $table->string('phone');
            $table->text('shipping_address');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Hapus tabel order kalau rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
