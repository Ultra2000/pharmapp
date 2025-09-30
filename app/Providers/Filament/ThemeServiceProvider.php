<?php

namespace App\Providers\Filament;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Assets\Css;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        FilamentColor::register([
            'primary' => Color::hex('#009E60'),
            'secondary' => Color::hex('#007A4B'),
            'gray' => Color::hex('#f5f5f5'),
        ]);
        
        Filament::registerStyles([
            Css::make('custom-theme', __DIR__ . '/../../resources/css/filament/admin/theme.css'),
        ]);
    }
}
