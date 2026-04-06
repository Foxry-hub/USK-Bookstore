<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahin kolom order ID Midtrans aktif per order.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('midtrans_order_id')->nullable()->after('midtrans_transaction_id');
        });
    }

    /**
     * Hapus kolom order ID Midtrans saat rollback.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('midtrans_order_id');
        });
    }
};
