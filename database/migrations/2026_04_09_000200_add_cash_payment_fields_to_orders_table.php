<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah field untuk metode pembayaran cash/tunai.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('cash_amount_paid', 12, 2)->nullable()->after('payment_method')->comment('Jumlah uang tunai yang diberikan oleh user');
            $table->decimal('cash_change', 12, 2)->nullable()->after('cash_amount_paid')->comment('Kembalian uang tunai');
            $table->timestamp('cash_payment_confirmed_at')->nullable()->after('cash_change')->comment('Waktu admin mengkonfirmasi pembayaran cash');
        });
    }

    /**
     * Hapus field cash payment kalau rollback.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cash_amount_paid', 'cash_change', 'cash_payment_confirmed_at']);
        });
    }
};
