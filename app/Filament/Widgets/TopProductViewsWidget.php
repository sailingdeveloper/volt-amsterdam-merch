<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TopProductViewsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Top Product Views';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PageView::query()
                    ->select('product_id', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT session_id) as unique_visitors'))
                    ->whereNotNull('product_id')
                    ->groupBy('product_id')
                    ->orderByDesc('unique_visitors')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Unique')
                    ->numeric(),
            ])
            ->paginated(false);
    }

    /**
     * @return string
     */
    public function getTableRecordKey(Model $record): string
    {
        return (string) $record->product_id;
    }
}
