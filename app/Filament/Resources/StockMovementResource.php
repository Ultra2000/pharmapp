<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Filament\Resources\StockMovementResource\RelationManagers;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    protected static ?string $navigationGroup = 'Gestion de stock';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $modelLabel = 'Mouvement de stock';
    
    protected static ?string $pluralModelLabel = 'Mouvements de stock';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Produit'),
                    
                Forms\Components\Select::make('type')
                    ->options(StockMovement::typeOptions())
                    ->required()
                    ->default(StockMovement::TYPE_IN)
                    ->reactive(),
                    
                Forms\Components\Select::make('reason')
                    ->options(StockMovement::reasonOptions())
                    ->required()
                    ->default(StockMovement::REASON_PURCHASE),
                    
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->label('Quantité'),
                    
                Forms\Components\TextInput::make('unit_price')
                    ->numeric()
                    ->prefix('€')
                    ->required()
                    ->label('Prix unitaire'),
                    
                Forms\Components\DateTimePicker::make('date')
                    ->required()
                    ->default(now()),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entrée' => 'success',
                        'sortie' => 'danger',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motif')
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Prix unitaire')
                    ->money('eur')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListStockMovements::route('/'),
            'create' => Pages\CreateStockMovement::route('/create'),
            'edit' => Pages\EditStockMovement::route('/{record}/edit'),
        ];
    }
}
