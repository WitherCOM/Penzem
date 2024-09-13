<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Support\CurrencyConverter;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Query\Builder;

class CategorySpending extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(Category::query())
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('amount')
                    ->getStateUsing(function(Category $record) {
                        return $record->allBudgets()->sum(fn ($budget) => CurrencyConverter::convert('HUF', $budget->currency->name,$budget->amount)) . ' Ft';
                    })
            ]);
    }
}
