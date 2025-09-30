<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class PharmacyKpiWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Calculs pour aujourd'hui
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // CA du jour
        $todaySales = Sale::whereDate('created_at', $today)->sum('total_amount');
        $yesterdaySales = Sale::whereDate('created_at', $yesterday)->sum('total_amount');
        $salesTrend = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        // Ventes du mois
        $monthSales = Sale::where('created_at', '>=', $thisMonth)->sum('total_amount');
        $lastMonthSales = Sale::whereBetween('created_at', [$lastMonth, $thisMonth])->sum('total_amount');
        $monthTrend = $lastMonthSales > 0 ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        // Alertes stock
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock')->count();
        $expiringSoonCount = Product::where('expiry_date', '<=', now()->addMonths(3))
            ->where('expiry_date', '>', now())
            ->count();

        // Nombre de ventes aujourd'hui
        $todayTransactions = Sale::whereDate('created_at', $today)->count();

        return [
            Stat::make('CA Aujourd\'hui', '€ ' . number_format($todaySales, 2))
                ->description($salesTrend >= 0 ? '+' . number_format($salesTrend, 1) . '% vs hier' : number_format($salesTrend, 1) . '% vs hier')
                ->descriptionIcon($salesTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getSalesChart()),

            Stat::make('CA du Mois', '€ ' . number_format($monthSales, 2))
                ->description($monthTrend >= 0 ? '+' . number_format($monthTrend, 1) . '% vs mois dernier' : number_format($monthTrend, 1) . '% vs mois dernier')
                ->descriptionIcon($monthTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Ventes Aujourd\'hui', $todayTransactions)
                ->description('Transactions effectuées')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Alertes Stock', $lowStockCount + $expiringSoonCount)
                ->description($lowStockCount . ' en rupture, ' . $expiringSoonCount . ' périmés bientôt')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount + $expiringSoonCount > 0 ? 'warning' : 'success'),
        ];
    }

    private function getSalesChart(): array
    {
        // Graphique des 7 derniers jours
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sales = Sale::whereDate('created_at', $date)->sum('total_amount');
            $data[] = round($sales, 2);
        }
        return $data;
    }
}
