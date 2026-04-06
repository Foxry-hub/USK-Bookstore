<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahin kolom transaksi payment gateway ke tabel orders.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('midtrans_transaction_id')->nullable()->after('note');
            $table->string('midtrans_transaction_status')->nullable()->after('midtrans_transaction_id');
            $table->string('midtrans_payment_type')->nullable()->after('midtrans_transaction_status');
            $table->string('midtrans_fraud_status')->nullable()->after('midtrans_payment_type');
            $table->timestamp('paid_at')->nullable()->after('midtrans_fraud_status');
        });
    }

    /**
     * Hapus kolom transaksi payment gateway saat rollback.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'midtrans_transaction_id',
                'midtrans_transaction_status',
                'midtrans_payment_type',
                'midtrans_fraud_status',
                'paid_at',
            ]);
        });
    }
};
