<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Filament\Resources;

use App\Filament\Resources\PageViewResource\Pages;
use App\Models\PageView;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageViewResource extends Resource
{
    protected static ?string $model = PageView::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 100;

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('path')
                    ->label('Page')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->formatStateUsing(fn (?string $state, PageView $record): string => $state ? $state . ', ' . ($record->country ?? '') : '-')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('referrer')
                    ->label('Referrer')
                    ->limit(30)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state),
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('country')
                    ->options(fn () => PageView::whereNotNull('country')->distinct()->pluck('country', 'country')->toArray())
                    ->searchable(),
                Tables\Filters\SelectFilter::make('city')
                    ->options(fn () => PageView::whereNotNull('city')->distinct()->pluck('city', 'city')->toArray())
                    ->searchable(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPageViews::route('/'),
        ];
    }
}
