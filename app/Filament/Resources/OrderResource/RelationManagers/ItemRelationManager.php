<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemRelationManager extends RelationManager
{
    protected static string $relationship = 'item';

    protected static ?string $title = 'Order Items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product'),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Unit Price')
                    ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.')),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.')),
            ])
            ->filters([])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
