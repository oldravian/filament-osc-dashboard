<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Projects', Project::query()->count()),
            Stat::make('Categories', Category::query()->count()),
            Stat::make('Technologies', Technology::query()->count()),
        ];
    }
}
