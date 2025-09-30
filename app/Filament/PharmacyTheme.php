<?php

namespace App\Filament;

use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;

class PharmacyTheme extends \Filament\Themes\Theme
{
    public function getId(): string
    {
        return 'pharmacy';
    }

    public function getAssets(): array
    {
        return [
            Css::make('pharmacy-styles', __DIR__ . '/../../resources/css/filament.css')
        ];
    }

    public function getColors(): array
    {
        return [
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => [
                50 => '#e6f5ee',
                100 => '#ccead2',
                200 => '#99d6b5',
                300 => '#66c197',
                400 => '#33ad7a',
                500 => '#009E60',
                600 => '#007A4B',
                700 => '#006b42',
                800 => '#005c39',
                900 => '#004d30',
                950 => '#003e27',
            ],
            'success' => Color::Green,
            'warning' => Color::Orange,
        ];
    }
}
