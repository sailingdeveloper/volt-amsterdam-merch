<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('name_nl')
                            ->label('Name (Dutch)'),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(3),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                        Forms\Components\RichEditor::make('description_nl')
                            ->label('Description (Dutch)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Stock')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->formatStateUsing(fn (?int $state): ?string => $state ? number_format($state / 100, 2, '.', '') : null)
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state ? (int) round((float) $state * 100) : null),
                        Forms\Components\TextInput::make('stock')
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->helperText('Leave empty for products with sizes'),
                        Forms\Components\Toggle::make('active')
                            ->default(true),
                    ])->columns(3),

                Forms\Components\Section::make('Sizes')
                    ->description('Configure available sizes and their stock. Leave empty for products without sizes.')
                    ->schema([
                        Forms\Components\KeyValue::make('sizes')
                            ->keyLabel('Size')
                            ->valueLabel('Stock')
                            ->keyPlaceholder('e.g. XS, S, M, L, XL, XXL')
                            ->valuePlaceholder('Stock quantity')
                            ->addActionLabel('Add size')
                            ->dehydrateStateUsing(function (?array $state): ?array {
                                if ($state === null) {
                                    return null;
                                }

                                $result = [];
                                foreach ($state as $size => $stock) {
                                    $result[$size] = (int) $stock;
                                }

                                return count($result) > 0 ? $result : null;
                            }),
                    ]),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Primary Image')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor(),
                        Forms\Components\FileUpload::make('images')
                            ->label('Additional Images')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->imageEditor()
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn (int $state): string => '€ ' . number_format($state / 100, 2, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('page_views_count')
                    ->label('Views')
                    ->counts('pageViews')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
