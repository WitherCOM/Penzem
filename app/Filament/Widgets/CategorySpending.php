<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Support\CurrencyConverter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CategorySpending extends BaseWidget
{
    public $dateFilterData = [];


    public function table(Table $table): Table
    {

        return $table
            ->query(Category::query())
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('amount')
                    ->getStateUsing(function(Category $record) {
                        return $record->allBudgets($this->dateFilterData['date_from'], $this->dateFilterData['date_to'])->sum(fn ($budget) => CurrencyConverter::convert('HUF', $budget->currency->name,$budget->amount)) . ' Ft';
                    })
            ])
            ->filters(
                [
                    Filter::make('date')
                        ->form([
                            DatePicker::make('date_from')
                                ->default(now()->startOfMonth()),
                            DatePicker::make('date_to')
                                ->default(now()->endOfMonth()),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            $this->dateFilterData = $data;
                            return $query;
                        })
                ]
            );
    }
}
