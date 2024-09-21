<?php

namespace App\Filament\Resources\BudgetResource\Widgets;

use App\Enums\Frequency;
use App\Filament\Resources\BudgetResource\Pages\ListBudgets;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BudgetSumPrice extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListBudgets::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Spending', $this->getPageTableQuery()->get()->sum(fn ($budget) => $budget->currency->convertTo($budget->amount, 'HUF')) . ' Ft'),
            Stat::make('Spending periodic', $this->getPageTableQuery()->where('frequency',Frequency::PERIODIC)->get()->sum(fn ($budget) => $budget->currency->convertTo($budget->amount, 'HUF')) . ' Ft')
        ];
    }
}
