<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Produits en rupture';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<=', 'min_stock')
                    ->orderBy('stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock actuel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Stock minimum')
                    ->sortable(),
            ])
            ->defaultSort('stock', 'asc');
    }
}
