<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class QuickSalesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        // Ventes d'aujourd'hui
        $todaySales = Sale::whereDate('created_at', today())->count();
        $todayRevenue = Sale::whereDate('created_at', today())->sum('total_amount');
        
        // Ventes de cette semaine
        $weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        
        // Produit le plus vendu aujourd'hui
        $topProduct = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('sale', function($query) {
                $query->whereDate('created_at', today());
            })
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->with('product')
            ->first();

        // Moyenne des ventes par heure
        $currentHour = now()->hour;
        $avgSalesPerHour = $currentHour > 0 ? round($todaySales / $currentHour, 1) : $todaySales;

        return [
            Stat::make('Ventes Aujourd\'hui', $todaySales)
                ->description('💰 ' . number_format($todayRevenue, 2) . '€')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success')
                ->chart([7, 12, 18, 25, 22, 27, $todaySales]),

            Stat::make('Ventes cette Semaine', $weekSales)
                ->description('📈 Progression hebdomadaire')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Produit Top', $topProduct ? $topProduct->product->name ?? 'Aucun' : 'Aucun')
                ->description($topProduct ? "🔥 {$topProduct->total_qty} vendus" : '📦 Aucune vente')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning'),

            Stat::make('Cadence', $avgSalesPerHour . '/h')
                ->description('⚡ Ventes par heure')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
