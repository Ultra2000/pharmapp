<?php

namespace App\Filament\Widgets;

use App\Services\AlertService;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class RealTimeAlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.real-time-alerts';
    protected static ?int $sort = 0;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function getAlerts(): array
    {
        $alertService = new AlertService();
        return $alertService->getAllAlerts();
    }

    public function getCriticalAlerts(): array
    {
        $alertService = new AlertService();
        return $alertService->getAlertsByPriority(AlertService::PRIORITY_CRITICAL);
    }

    public function getHighPriorityAlerts(): array
    {
        $alertService = new AlertService();
        return $alertService->getAlertsByPriority(AlertService::PRIORITY_HIGH);
    }

    public function getTotalAlertsCount(): int
    {
        $alerts = $this->getAlerts();
        $total = 0;
        
        foreach ($alerts as $category) {
            $total += count($category);
        }
        
        return $total;
    }
}
