<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;

class AlertService
{
    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_LOW = 'low';

    public function getAllAlerts(): array
    {
        return [
            'stock' => $this->getStockAlerts(),
            'expiry' => $this->getExpiryAlerts(),
            'sales' => $this->getSalesAlerts(),
            'performance' => $this->getPerformanceAlerts(),
        ];
    }

    public function getStockAlerts(): array
    {
        $alerts = [];
        
        // Produits en rupture de stock
        $outOfStock = Product::where('stock', 0)->get();
        foreach ($outOfStock as $product) {
            $alerts[] = [
                'id' => 'stock_out_' . $product->id,
                'type' => 'stock_out',
                'priority' => self::PRIORITY_CRITICAL,
                'title' => 'RUPTURE DE STOCK',
                'message' => "Le produit '{$product->name}' est en rupture de stock",
                'product' => $product,
                'action' => 'Commander immédiatement',
                'icon' => '🚨',
                'color' => 'danger',
                'created_at' => now(),
            ];
        }

        // Stock faible
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')
                           ->where('stock', '>', 0)
                           ->get();
        foreach ($lowStock as $product) {
            $alerts[] = [
                'id' => 'stock_low_' . $product->id,
                'type' => 'stock_low',
                'priority' => self::PRIORITY_HIGH,
                'title' => 'STOCK FAIBLE',
                'message' => "Le produit '{$product->name}' a un stock faible ({$product->stock} restants)",
                'product' => $product,
                'action' => 'Prévoir réapprovisionnement',
                'icon' => '⚠️',
                'color' => 'warning',
                'created_at' => now(),
            ];
        }

        // Stock excédentaire (plus de 6 mois sans vente)
        $excessStock = Product::whereDoesntHave('saleItems', function ($query) {
            $query->whereHas('sale', function ($saleQuery) {
                $saleQuery->where('created_at', '>=', now()->subMonths(6));
            });
        })->where('stock', '>', 0)->get();

        foreach ($excessStock as $product) {
            $alerts[] = [
                'id' => 'stock_excess_' . $product->id,
                'type' => 'stock_excess',
                'priority' => self::PRIORITY_MEDIUM,
                'title' => 'STOCK EXCÉDENTAIRE',
                'message' => "Le produit '{$product->name}' n'a pas été vendu depuis 6 mois",
                'product' => $product,
                'action' => 'Envisager une promotion',
                'icon' => '📦',
                'color' => 'info',
                'created_at' => now(),
            ];
        }

        return $alerts;
    }

    public function getExpiryAlerts(): array
    {
        $alerts = [];

        // Produits expirés
        $expired = Product::where('expiry_date', '<', now())->get();
        foreach ($expired as $product) {
            $alerts[] = [
                'id' => 'expiry_expired_' . $product->id,
                'type' => 'expired',
                'priority' => self::PRIORITY_CRITICAL,
                'title' => 'PRODUIT EXPIRÉ',
                'message' => "Le produit '{$product->name}' a expiré le " . $product->expiry_date->format('d/m/Y'),
                'product' => $product,
                'action' => 'Retirer de la vente immédiatement',
                'icon' => '💀',
                'color' => 'danger',
                'created_at' => now(),
            ];
        }

        // Expire dans 30 jours
        $expiringSoon = Product::whereBetween('expiry_date', [now(), now()->addDays(30)])
                              ->orderBy('expiry_date')
                              ->get();
        foreach ($expiringSoon as $product) {
            $daysLeft = now()->diffInDays($product->expiry_date);
            $alerts[] = [
                'id' => 'expiry_soon_' . $product->id,
                'type' => 'expiring_soon',
                'priority' => self::PRIORITY_HIGH,
                'title' => 'EXPIRE BIENTÔT',
                'message' => "Le produit '{$product->name}' expire dans {$daysLeft} jours",
                'product' => $product,
                'action' => $daysLeft <= 7 ? 'Promotion urgente' : 'Programmer une promotion',
                'icon' => '⏰',
                'color' => 'warning',
                'created_at' => now(),
            ];
        }

        // Expire dans 3 mois
        $expiringLater = Product::whereBetween('expiry_date', [now()->addDays(31), now()->addMonths(3)])
                               ->orderBy('expiry_date')
                               ->get();
        foreach ($expiringLater as $product) {
            $daysLeft = now()->diffInDays($product->expiry_date);
            $alerts[] = [
                'id' => 'expiry_later_' . $product->id,
                'type' => 'expiring_later',
                'priority' => self::PRIORITY_LOW,
                'title' => 'PÉREMPTION À SURVEILLER',
                'message' => "Le produit '{$product->name}' expire dans {$daysLeft} jours",
                'product' => $product,
                'action' => 'Surveiller les ventes',
                'icon' => '📅',
                'color' => 'info',
                'created_at' => now(),
            ];
        }

        return $alerts;
    }

    public function getSalesAlerts(): array
    {
        $alerts = [];

        // Pas de ventes aujourd'hui après 12h
        $today = Carbon::today();
        $todaySales = Sale::whereDate('created_at', $today)->count();
        
        if ($todaySales == 0 && now()->hour >= 12) {
            $alerts[] = [
                'id' => 'sales_none_today',
                'type' => 'no_sales',
                'priority' => self::PRIORITY_MEDIUM,
                'title' => 'AUCUNE VENTE AUJOURD\'HUI',
                'message' => 'Aucune vente enregistrée depuis ce matin',
                'action' => 'Vérifier le système de caisse',
                'icon' => '💰',
                'color' => 'warning',
                'created_at' => now(),
            ];
        }

        // Ventes en baisse (comparaison avec la même période la semaine dernière)
        $lastWeekSales = Sale::whereBetween('created_at', [
            $today->copy()->subWeek(),
            $today->copy()->subWeek()->addDay()
        ])->sum('total_amount');

        $todaySalesAmount = Sale::whereDate('created_at', $today)->sum('total_amount');

        if ($lastWeekSales > 0 && $todaySalesAmount < ($lastWeekSales * 0.7)) {
            $decrease = round((($lastWeekSales - $todaySalesAmount) / $lastWeekSales) * 100);
            $alerts[] = [
                'id' => 'sales_decrease',
                'type' => 'sales_down',
                'priority' => self::PRIORITY_MEDIUM,
                'title' => 'BAISSE DES VENTES',
                'message' => "Les ventes ont baissé de {$decrease}% par rapport à la semaine dernière",
                'action' => 'Analyser les causes',
                'icon' => '📉',
                'color' => 'warning',
                'created_at' => now(),
            ];
        }

        return $alerts;
    }

    public function getPerformanceAlerts(): array
    {
        $alerts = [];

        // Produits populaires en stock faible - compatible SQLite
        $allProducts = Product::whereColumn('stock', '<=', 'min_stock')->get();
        
        foreach ($allProducts as $product) {
            // Compter les ventes récentes pour ce produit
            $recentSalesCount = $product->saleItems()
                ->whereHas('sale', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(7));
                })
                ->count();
            
            // Si le produit a eu au moins 3 ventes récentes
            if ($recentSalesCount >= 3) {
                $alerts[] = [
                    'id' => 'performance_popular_low_' . $product->id,
                    'type' => 'popular_low_stock',
                    'priority' => self::PRIORITY_HIGH,
                    'title' => 'PRODUIT POPULAIRE EN STOCK FAIBLE',
                    'message' => "Le produit '{$product->name}' se vend bien ({$recentSalesCount} ventes cette semaine) mais le stock est faible ({$product->stock} unités)",
                    'product' => $product,
                    'action' => 'Commander en priorité',
                    'icon' => '🔥',
                    'color' => 'warning',
                    'created_at' => now(),
                ];
            }
        }

        return $alerts;
    }

    public function getAlertsByPriority(string $priority): array
    {
        $allAlerts = $this->getAllAlerts();
        $filtered = [];

        foreach ($allAlerts as $category => $alerts) {
            foreach ($alerts as $alert) {
                if ($alert['priority'] === $priority) {
                    $filtered[] = $alert;
                }
            }
        }

        return $filtered;
    }

    public function getCriticalAlertsCount(): int
    {
        return count($this->getAlertsByPriority(self::PRIORITY_CRITICAL));
    }

    public function getTotalAlertsCount(): int
    {
        $allAlerts = $this->getAllAlerts();
        $count = 0;

        foreach ($allAlerts as $alerts) {
            $count += count($alerts);
        }

        return $count;
    }
}
