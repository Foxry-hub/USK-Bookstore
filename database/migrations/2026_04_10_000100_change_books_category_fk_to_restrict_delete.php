<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cegah penghapusan kategori jika masih dipakai buku.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    /**
     * Kembalikan perilaku lama agar rollback aman.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['category_id']);

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
