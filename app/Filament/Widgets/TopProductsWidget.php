<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SaleItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = '🏆 Top Produits (30 derniers jours)';
    protected static ?int $limit = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->whereHas('saleItems')
                    ->withSum('saleItems as total_sold', 'quantity')
                    ->withSum('saleItems as total_revenue', 'total_price')
                    ->orderByDesc('total_sold')
            )
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('image')
                        ->size(40)
                        ->circular()
                        ->defaultImageUrl(url('/images/pill-default.png')),
                    Tables\Columns\TextColumn::make('name')
                        ->weight('bold')
                        ->color('primary')
                        ->searchable(),
                    Tables\Columns\TextColumn::make('dci')
                        ->color('gray')
                        ->size('sm'),
                ])->space(2),
                
                Tables\Columns\Layout\Grid::make(3)
                    ->schema([
                        Tables\Columns\TextColumn::make('total_sold')
                            ->label('Vendus')
                            ->badge()
                            ->color('success')
                            ->suffix(' unités'),
                        
                        Tables\Columns\TextColumn::make('total_revenue')
                            ->label('CA')
                            ->money('EUR')
                            ->color('info'),
                        
                        Tables\Columns\TextColumn::make('stock')
                            ->label('Stock')
                            ->badge()
                            ->color(function ($record) {
                                if ($record->stock <= $record->min_stock) return 'danger';
                                if ($record->stock <= $record->min_stock * 2) return 'warning';
                                return 'success';
                            }),
                    ]),
                
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('sale_price')
                            ->label('Prix')
                            ->money('EUR')
                            ->grow(false),
                        
                        Tables\Columns\TextColumn::make('expiry_date')
                            ->label('Expire le')
                            ->date()
                            ->color(function ($record) {
                                $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                                if ($daysToExpiry < 90) return 'danger';
                                if ($daysToExpiry < 180) return 'warning';
                                return 'success';
                            })
                            ->grow(false),
                    ]),
                ])->collapsible(),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->paginated(false);
    }
}
