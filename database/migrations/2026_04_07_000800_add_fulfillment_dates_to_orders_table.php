<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('shipped_at')->nullable()->after('note');
            $table->timestamp('estimated_delivery_at')->nullable()->after('shipped_at');
            $table->timestamp('received_at')->nullable()->after('estimated_delivery_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['shipped_at', 'estimated_delivery_at', 'received_at']);
        });
    }
};