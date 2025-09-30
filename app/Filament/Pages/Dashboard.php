<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ExpiringProductsWidget;
use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\TopProductsWidget;
use App\Models\Sale;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    public function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Exporter les ventes (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->size(ActionSize::Large)
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Date de début')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Date de fin')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $fileName = 'ventes-' . Carbon::parse($data['start_date'])->format('d-m-Y') . '-' . Carbon::parse($data['end_date'])->format('d-m-Y') . '.csv';
                    
                    $sales = Sale::query()
                        ->whereBetween('created_at', [$data['start_date'], $data['end_date']])
                        ->with(['items.product', 'user'])
                        ->get();

                    $headers = [
                        'ID',
                        'Date',
                        'Utilisateur',
                        'Produits',
                        'Quantité',
                        'Prix unitaire',
                        'Total',
                    ];

                    $callback = function () use ($sales, $headers) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, $headers);

                        foreach ($sales as $sale) {
                            foreach ($sale->items as $item) {
                                fputcsv($file, [
                                    $sale->id,
                                    $sale->created_at->format('d/m/Y'),
                                    $sale->user->name ?? 'Utilisateur supprimé',
                                    $item->product->name,
                                    $item->quantity,
                                    $item->unit_price,
                                    $item->total_price,
                                ]);
                            }
                        }

                        fclose($file);
                    };

                    response()->stream($callback, 200, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                    ]);
                }),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PharmacyKpiWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SalesChart::class,
            \App\Filament\Widgets\TopProductsWidget::class,
            \App\Filament\Widgets\AlertsWidget::class,
            \App\Filament\Widgets\ExpiringProductsWidget::class,
            \App\Filament\Widgets\LowStockProductsWidget::class,
        ];
    }
}
