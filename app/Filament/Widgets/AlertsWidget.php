<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = '🚨 Alertes Prioritaires';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where(function ($query) {
                        $query->whereColumn('stock', '<=', 'min_stock')
                              ->orWhere('expiry_date', '<=', now()->addMonths(3));
                    })
                    ->orderBy('stock', 'asc')
                    ->orderBy('expiry_date', 'asc')
            )
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('alert_type')
                        ->label('Type d\'alerte')
                        ->getStateUsing(function ($record) {
                            if ($record->stock == 0) return '🔴 RUPTURE DE STOCK';
                            if ($record->stock <= $record->min_stock) return '🟠 STOCK FAIBLE';
                            
                            $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                            if ($daysToExpiry <= 30) return '🔴 EXPIRE BIENTÔT';
                            if ($daysToExpiry <= 90) return '🟡 PÉREMPTION PROCHE';
                            
                            return '🟢 OK';
                        })
                        ->badge()
                        ->color(function ($record) {
                            if ($record->stock == 0) return 'danger';
                            if ($record->stock <= $record->min_stock) return 'warning';
                            
                            $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                            if ($daysToExpiry <= 30) return 'danger';
                            if ($daysToExpiry <= 90) return 'warning';
                            
                            return 'success';
                        }),

                    Tables\Columns\TextColumn::make('name')
                        ->weight('bold')
                        ->searchable(),
                        
                    Tables\Columns\TextColumn::make('dci')
                        ->color('gray')
                        ->size('sm'),
                ])->space(1),

                Tables\Columns\Layout\Grid::make(2)
                    ->schema([
                        Tables\Columns\TextColumn::make('stock_info')
                            ->label('Stock')
                            ->getStateUsing(function ($record) {
                                return $record->stock . ' / ' . $record->min_stock . ' (min)';
                            })
                            ->badge()
                            ->color(function ($record) {
                                if ($record->stock == 0) return 'danger';
                                if ($record->stock <= $record->min_stock) return 'warning';
                                return 'success';
                            }),

                        Tables\Columns\TextColumn::make('expiry_info')
                            ->label('Péremption')
                            ->getStateUsing(function ($record) {
                                $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                                if ($daysToExpiry < 0) return 'EXPIRÉ';
                                return 'Dans ' . $daysToExpiry . ' jours';
                            })
                            ->badge()
                            ->color(function ($record) {
                                $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                                if ($daysToExpiry < 0) return 'danger';
                                if ($daysToExpiry <= 30) return 'danger';
                                if ($daysToExpiry <= 90) return 'warning';
                                return 'success';
                            }),
                    ]),

                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\TextColumn::make('action_needed')
                        ->label('Action recommandée')
                        ->getStateUsing(function ($record) {
                            if ($record->stock == 0) return '🛒 COMMANDE URGENTE';
                            if ($record->stock <= $record->min_stock) return '📦 Réapprovisionner';
                            
                            $daysToExpiry = now()->diffInDays($record->expiry_date, false);
                            if ($daysToExpiry <= 30) return '💥 Promotion ou retour';
                            if ($daysToExpiry <= 90) return '🏷️ Envisager une promotion';
                            
                            return '✅ Surveiller';
                        })
                        ->color('info'),
                ])->collapsible(),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ])
            ->paginated([10, 25, 50]);
    }
}
