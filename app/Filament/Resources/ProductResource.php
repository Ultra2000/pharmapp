<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Produits';

    protected static ?string $navigationGroup = 'Gestion de stock';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),
                Forms\Components\TextInput::make('dci')
                    ->label('DCI')
                    ->required(),
                Forms\Components\TextInput::make('dosage')
                    ->label('Dosage')
                    ->required(),
                Forms\Components\TextInput::make('form')
                    ->label('Forme')
                    ->required(),
                Forms\Components\TextInput::make('barcode')
                    ->label('Code-barres')
                    ->required(),
                Forms\Components\TextInput::make('purchase_price')
                    ->label('Prix d\'achat')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('sale_price')
                    ->label('Prix de vente')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('min_stock')
                    ->label('Stock minimum')
                    ->numeric()
                    ->default(5)
                    ->required(),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('Date de péremption')
                    ->required(),
                Forms\Components\TextInput::make('lot_number')
                    ->label('Numéro de lot')
                    ->required(),
                Forms\Components\Select::make('supplier_id')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->directory('products'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dci')
                    ->label('DCI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Code-barres'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Prix')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Péremption')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
