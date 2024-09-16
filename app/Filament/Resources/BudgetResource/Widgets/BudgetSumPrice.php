<?php

namespace App\Filament\Resources\BudgetResource\Widgets;

use App\Filament\Resources\BudgetResource\Pages\ListBudgets;
use App\Support\CurrencyConverter;
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
        $query = $this->getPageTableQuery()->newQuery();
        unset($query->getQuery()->wheres[0]);
        return [
            Stat::make('Spending', $query->get()->sum(fn ($budget) => CurrencyConverter::convert('HUF', $budget->currency->name,$budget->amount)) . ' Ft')
        ];
    }
}
