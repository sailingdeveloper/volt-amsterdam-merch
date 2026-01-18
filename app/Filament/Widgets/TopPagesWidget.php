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

class TopPagesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Top Pages';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PageView::query()
                    ->select('path', DB::raw('COUNT(*) as views'), DB::raw('COUNT(DISTINCT session_id) as unique_visitors'))
                    ->groupBy('path')
                    ->orderByDesc('unique_visitors')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('path')
                    ->label('Page'),
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
        return $record->path;
    }
}
