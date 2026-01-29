<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'stripe_session_id',
        'stripe_payment_intent_id',
        'payment_provider',
        'payment_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_postal_code',
        'billing_country',
        'subtotal',
        'fee',
        'total',
        'status',
        'locale',
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

    /**
     * @return HasOne<Cart, $this>
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function getOrderNumberAttribute(): string
    {
        return str_pad($this->id, 6, '0', STR_PAD_LEFT);
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
