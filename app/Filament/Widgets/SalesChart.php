<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = '📈 Évolution des Ventes';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30d';

    protected function getData(): array
    {
        $period = $this->filter;
        
        switch ($period) {
            case '7d':
                return $this->getWeeklyData();
            case '30d':
                return $this->getMonthlyData();
            case '12m':
                return $this->getYearlyData();
            default:
                return $this->getMonthlyData();
        }
    }

    protected function getFilters(): ?array
    {
        return [
            '7d' => '7 derniers jours',
            '30d' => '30 derniers jours',
            '12m' => '12 derniers mois',
        ];
    }

    private function getWeeklyData(): array
    {
        $sales = [];
        $quantities = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->locale('fr')->format('D d/m');
            
            $daySales = Sale::whereDate('created_at', $date)->sum('total_amount');
            
            // Utiliser une relation directe pour éviter les jointures ambiguës
            $dayQuantities = \App\Models\SaleItem::whereHas('sale', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })->sum('quantity');

            $sales[] = round($daySales, 2);
            $quantities[] = $dayQuantities ?: 0;
            $labels[] = $dayName;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d\'affaires (€)',
                    'data' => $sales,
                    'borderColor' => '#009E60',
                    'backgroundColor' => 'rgba(0, 158, 96, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Unités vendues',
                    'data' => $quantities,
                    'borderColor' => '#FF6B35',
                    'backgroundColor' => 'rgba(255, 107, 53, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ]
            ],
            'labels' => $labels,
        ];
    }

    private function getMonthlyData(): array
    {
        $sales = [];
        $quantities = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayName = $date->format('d/m');
            
            $daySales = Sale::whereDate('created_at', $date)->sum('total_amount');
            
            // Utiliser une relation directe pour éviter les jointures ambiguës
            $dayQuantities = \App\Models\SaleItem::whereHas('sale', function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })->sum('quantity');

            $sales[] = round($daySales, 2);
            $quantities[] = $dayQuantities ?: 0;
            $labels[] = $dayName;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d\'affaires (€)',
                    'data' => $sales,
                    'borderColor' => '#009E60',
                    'backgroundColor' => 'rgba(0, 158, 96, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getYearlyData(): array
    {
        $sales = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->locale('fr')->format('M Y');
            
            $monthSales = Sale::whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month)
                             ->sum('total_amount');

            $sales[] = round($monthSales, 2);
            $labels[] = $monthName;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Chiffre d\'affaires mensuel (€)',
                    'data' => $sales,
                    'borderColor' => '#009E60',
                    'backgroundColor' => 'rgba(0, 158, 96, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Chiffre d\'affaires (€)'
                    ]
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => $this->filter === '7d',
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Unités vendues'
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
