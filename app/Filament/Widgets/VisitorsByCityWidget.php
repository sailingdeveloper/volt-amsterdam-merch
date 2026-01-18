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

class VisitorsByCityWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Visitors by City';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PageView::query()
                    ->select(
                        'city',
                        'country',
                        DB::raw('COUNT(*) as views'),
                        DB::raw('COUNT(DISTINCT session_id) as unique_visitors'),
                    )
                    ->whereNotNull('city')
                    ->groupBy('city', 'country')
                    ->orderByDesc('unique_visitors')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->formatStateUsing(fn (string $state, Model $record): string => $state . ', ' . ($record->country ?? '')),
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->numeric(),
                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label('Unique')
                    ->numeric()
                    ->sortable(),
            ])
            ->paginated(false);
    }

    /**
     * @return string
     */
    public function getTableRecordKey(Model $record): string
    {
        return $record->city . '_' . $record->country;
    }

}
