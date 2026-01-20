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

class TrafficSourcesWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Traffic Sources';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PageView::query()
                    ->select('referrer', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT session_id) as unique_visitors'))
                    ->whereNotNull('referrer')
                    ->groupBy('referrer')
                    ->orderByDesc('visits')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('referrer')
                    ->label('Source')
                    ->formatStateUsing(fn (string $state): string => $this->formatReferrer($state))
                    ->tooltip(fn (string $state): string => $state)
                    ->limit(30),
                Tables\Columns\TextColumn::make('visits')
                    ->label('Visits')
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
        return md5($record->referrer);
    }

    /**
     * @return string
     */
    private function formatReferrer(string $referrer): string
    {
        $parsed = parse_url($referrer);

        if (empty($parsed['host'])) {
            return $referrer;
        }

        // Remove www. prefix from host.
        $host = preg_replace('/^www\./', '', $parsed['host']);
        $path = $parsed['path'] ?? '';

        return $host . $path;
    }
}
