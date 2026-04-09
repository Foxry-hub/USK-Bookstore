<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = ['category_id', 'title', 'isbn', 'author', 'price', 'stock', 'image_url', 'image_urls', 'description', 'detail'];

    protected $casts = [
        'image_urls' => 'array',
        'stock' => 'integer',
    ];

    /**
     * Kembalikan daftar gambar galeri tanpa duplikasi dan tanpa nilai kosong.
     * Gambar utama tetap ditaruh di posisi pertama jika tersedia.
     *
     * @return array<int, string>
     */
    public function galleryImages(): array
    {
        $images = [];

        if (!empty($this->image_url)) {
            $images[] = $this->image_url;
        }

        foreach (($this->image_urls ?? []) as $url) {
            if (is_string($url) && trim($url) !== '') {
                $images[] = trim($url);
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * Relasi ke kategori biar setiap buku punya kelompok yang jelas.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke item order untuk histori transaksi buku ini.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
