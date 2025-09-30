<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dci')
                    ->label('DCI')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dosage')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('form')
                    ->label('Forme')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('purchase_price')
                    ->label('Prix d\'achat')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('sale_price')
                    ->label('Prix de vente')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dci')
                    ->label('DCI')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dosage')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('form')
                    ->label('Forme')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Prix d\'achat')
                    ->money('eur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Prix de vente')
                    ->money('eur')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
