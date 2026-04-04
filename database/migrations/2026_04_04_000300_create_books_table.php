<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bikin tabel buku sebagai katalog utama toko.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Hapus tabel buku kalau rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
