<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Currency;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentMonth extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('This month', Currency::formatValue(Budget::whereBetween('date',[Carbon::today()->startOfMonth(), Carbon::today()])->get()->sum(fn ($budget) => $budget->currency->convertTo($budget->amount, 'HUF')), 'HUF'))
        ];
    }
}
