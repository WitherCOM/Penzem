<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentDay extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('This month', Budget::where('date', Carbon::now())->get()->sum(fn ($budget) => $budget->currency->convertTo($budget->amount, 'HUF')) . ' Ft'),
        ];
    }
}
