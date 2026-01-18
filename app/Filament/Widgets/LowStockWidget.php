<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LowStockWidget extends BaseWidget
{
    /**
     * Stock threshold constant.
     */
    protected const STOCK_THRESHOLD_LOW = 10;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low Stock Alert (< 10 items)';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getLowStockQuery())
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('')
                    ->disk('public')
                    ->size(40)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Stock Status')
                    ->state(fn (Product $record): HtmlString => $this->formatStockStatus($record))
                    ->html(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-pencil'),
            ])
            ->paginated(false)
            ->emptyStateHeading('All products are well-stocked')
            ->emptyStateDescription('No products have stock below 10 items.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    /**
     * @return Builder<Product>
     */
    private function getLowStockQuery(): Builder
    {
        return Product::query()
            ->where('active', true)
            ->where(function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->whereNull('sizes')
                        ->where('stock', '<', self::STOCK_THRESHOLD_LOW);
                })->orWhere(function (Builder $q) {
                    $q->whereNotNull('sizes');
                });
            })
            ->get()
            ->filter(fn (Product $product) => $this->hasLowStock($product))
            ->pluck('id')
            ->pipe(fn ($ids) => Product::whereIn('id', $ids));
    }

    private function hasLowStock(Product $product): bool
    {
        if ($product->hasSizes()) {
            foreach ($product->sizes as $size => $stock) {
                if ($stock < self::STOCK_THRESHOLD_LOW) {
                    return true;
                }
            }
            return false;
        } else {
            return $product->stock < self::STOCK_THRESHOLD_LOW;
        }
    }

    private function formatStockStatus(Product $product): HtmlString
    {
        $lineHtml = [];

        if ($product->hasSizes()) {
            foreach ($product->sizes as $size => $stock) {
                if ($stock < self::STOCK_THRESHOLD_LOW) {
                    $color = $this->determineStockColor($stock);
                    $lineHtml[] = "<span class=\"inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium {$color}\">{$size}: {$stock}</span>";
                }
            }
        } elseif ($product->stock < self::STOCK_THRESHOLD_LOW) {
            $color = $this->determineStockColor($product->stock);
            $lineHtml[] = "<span class=\"inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium {$color}\">{$product->stock} items</span>";
        }

        return new HtmlString(implode(' ', $lineHtml));
    }

    private function determineStockColor(int $stock): string
    {
        if ($stock === 0) {
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        } elseif ($stock < 5) {
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
        } else {
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        }
    }
}
