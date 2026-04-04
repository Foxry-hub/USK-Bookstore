<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'book_id', 'quantity', 'price', 'subtotal'];

    /**
     * Relasi ke order induk dari item ini.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relasi ke buku yang dibeli.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
