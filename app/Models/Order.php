<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'total_price',
        'payment_method',
        'status',
        'phone',
        'shipping_address',
        'note',
    ];

    /**
     * Relasi ke user yang melakukan checkout.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke detail item order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
