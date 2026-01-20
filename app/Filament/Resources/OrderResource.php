<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Customer Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('customer_email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('customer_phone')
                            ->label('Phone')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('locale')
                            ->label('Language')
                            ->formatStateUsing(fn (string $state): string => $state === 'nl' ? 'Nederlands' : 'English'),
                    ])->columns(4),

                Infolists\Components\Section::make('Billing Address')
                    ->schema([
                        Infolists\Components\TextEntry::make('billing_address_line1')
                            ->label('Address Line 1')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('billing_address_line2')
                            ->label('Address Line 2')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('billing_city')
                            ->label('City')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('billing_postal_code')
                            ->label('Postal Code')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('billing_country')
                            ->label('Country')
                            ->placeholder('-'),
                    ])->columns(3),

                Infolists\Components\Section::make('Order Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Order #'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'gray',
                                'canceled' => 'gray',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Order Date')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('subtotal')
                            ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.')),
                        Infolists\Components\TextEntry::make('fee')
                            ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.')),
                        Infolists\Components\TextEntry::make('total')
                            ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.'))
                            ->weight('bold'),
                    ])->columns(3),

                Infolists\Components\Section::make('Stripe')
                    ->schema([
                        Infolists\Components\TextEntry::make('stripe_session_id')
                            ->label('Session ID')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                            ->label('Payment Intent ID')
                            ->copyable(),
                    ])->columns(2)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('id', $direction)),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        'canceled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
