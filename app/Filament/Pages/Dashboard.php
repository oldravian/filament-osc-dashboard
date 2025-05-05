<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OverviewChart;
use App\Filament\Widgets\StatsOverview;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            OverviewChart::class,
        ];
    }
}
