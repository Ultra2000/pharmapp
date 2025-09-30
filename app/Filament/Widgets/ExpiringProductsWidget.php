<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiringProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Produits proche de la péremption';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('expiry_date', '<=', now()->addMonths(3))
                    ->where('expiry_date', '>', now())
                    ->orderBy('expiry_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Date de péremption')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable(),
            ])
            ->defaultSort('expiry_date', 'asc');
    }
}
