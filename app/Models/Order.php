<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'stripe_session_id',
        'stripe_payment_intent_id',
        'customer_email',
        'customer_name',
        'subtotal',
        'fee',
        'total',
        'status',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'fee' => 'integer',
        'total' => 'integer',
    ];

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function item(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total / 100, 2, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal / 100, 2, ',', '.');
    }

    public function getFormattedFeeAttribute(): string
    {
        return number_format($this->fee / 100, 2, ',', '.');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
