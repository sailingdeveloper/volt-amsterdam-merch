<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CartResource\Pages;
use App\Filament\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Cart Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Cart ID'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'warning',
                                'converted' => 'success',
                                'abandoned' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Activity')
                            ->dateTime(),
                    ])->columns(4),

                Infolists\Components\Section::make('Linked Order')
                    ->schema([
                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Order #')
                            ->placeholder('Not converted')
                            ->url(fn (Cart $record): ?string => $record->order_id
                                ? route('filament.admin.resources.orders.view', $record->order_id)
                                : null),
                        Infolists\Components\TextEntry::make('order.status')
                            ->label('Order Status')
                            ->badge()
                            ->placeholder('-')
                            ->color(fn (?string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('order.total')
                            ->label('Order Total')
                            ->placeholder('-')
                            ->formatStateUsing(fn (?int $state): string => $state
                                ? '€ ' . number_format($state / 100, 2, ',', '.')
                                : '-'),
                    ])->columns(3)
                    ->visible(fn (Cart $record): bool => $record->order_id !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Cart ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'warning',
                        'converted' => 'success',
                        'abandoned' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->placeholder('-')
                    ->url(fn (Cart $record): ?string => $record->order_id
                        ? route('filament.admin.resources.orders.view', $record->order_id)
                        : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'converted' => 'Converted',
                        'abandoned' => 'Abandoned',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'view' => Pages\ViewCart::route('/{record}'),
        ];
    }
}
