<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * Standard size ordering from smallest to largest.
     */
    public const SIZE_ORDER = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

    protected $fillable = [
        'name',
        'name_nl',
        'slug',
        'description',
        'description_nl',
        'price',
        'image',
        'images',
        'stock',
        'sizes',
        'active',
        'orderable',
    ];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'active' => 'boolean',
        'orderable' => 'boolean',
        'sizes' => 'array',
        'images' => 'array',
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

    public function hasSizes(): bool
    {
        return is_array($this->sizes) && count($this->sizes) > 0;
    }

    /**
     * Get sizes sorted in logical order (XS, S, M, L, XL, XXL, XXXL).
     *
     * @return array<string, int>
     */
    public function getOrderedSizesAttribute(): array
    {
        if ($this->hasSizes() === false) {
            return [];
        }

        $sizes = $this->sizes;
        $ordered = [];

        // First add sizes in standard order
        foreach (self::SIZE_ORDER as $size) {
            if (isset($sizes[$size])) {
                $ordered[$size] = $sizes[$size];
                unset($sizes[$size]);
            }
        }

        // Then add any custom sizes that weren't in the standard order
        foreach ($sizes as $size => $stock) {
            $ordered[$size] = $stock;
        }

        return $ordered;
    }

    public function getAvailableSizeAttribute(): array
    {
        if ($this->hasSizes() === false) {
            return [];
        }

        $availableSize = [];
        foreach ($this->sizes as $size => $stock) {
            if ($stock > 0) {
                $availableSize[$size] = $stock;
            }
        }

        return $availableSize;
    }

    public function isInStock(): bool
    {
        if ($this->hasSizes()) {
            return count($this->available_size) > 0;
        }

        return $this->stock > 0;
    }

    public function isSizeInStock(string $size): bool
    {
        if ($this->hasSizes() === false) {
            return true;
        }

        return isset($this->sizes[$size]) && $this->sizes[$size] > 0;
    }

    public function getStockForSize(string $size): int
    {
        if ($this->hasSizes() === false) {
            return $this->stock ?? 0;
        }

        return $this->sizes[$size] ?? 0;
    }

    public function decrementStockForSize(string $size, int $quantity = 1): void
    {
        if ($this->hasSizes() === false) {
            $this->decrement('stock', $quantity);
            return;
        }

        $sizes = $this->sizes;
        if (isset($sizes[$size])) {
            $sizes[$size] = max(0, $sizes[$size] - $quantity);
            $this->sizes = $sizes;
            $this->save();
        }
    }

    public function isOrderable(): bool
    {
        return $this->orderable && $this->isInStock();
    }

    public function getAllImageAttribute(): array
    {
        $images = [];

        if ($this->image) {
            $images[] = $this->image;
        }

        if (is_array($this->images)) {
            $images = array_merge($images, $this->images);
        }

        return $images;
    }
}
