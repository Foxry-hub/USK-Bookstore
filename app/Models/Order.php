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
        'midtrans_transaction_id',
        'midtrans_order_id',
        'midtrans_transaction_status',
        'midtrans_payment_type',
        'midtrans_fraud_status',
        'shipped_at',
        'estimated_delivery_at',
        'received_at',
        'paid_at',
        'cash_amount_paid',
        'cash_change',
        'cash_payment_confirmed_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'received_at' => 'datetime',
        'paid_at' => 'datetime',
        'cash_payment_confirmed_at' => 'datetime',
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
