<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'name_nl',
        'slug',
        'description',
        'description_nl',
        'price',
        'image',
        'stock',
        'active',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price / 100, 2, ',', '.');
    }

    public function getLocalizedNameAttribute(): string
    {
        if (app()->getLocale() === 'nl' && $this->name_nl) {
            return $this->name_nl;
        }

        return $this->name;
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        if (app()->getLocale() === 'nl' && $this->description_nl) {
            return $this->description_nl;
        }

        return $this->description;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
