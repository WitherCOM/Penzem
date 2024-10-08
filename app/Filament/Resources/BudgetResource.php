<?php

namespace App\Filament\Resources;

use App\Enums\Frequency;
use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('description'),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Select::make('currency_id')
                    ->required()
                    ->relationship('currency','name'),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Select::make('frequency')
                    ->required()
                    ->options(Frequency::class),
                SelectTree::make('categories')
                    ->statePath('categories')
                    ->live()
                    ->relationship('categories','name','parent_category_id')
                    ->afterStateUpdated(function (array $state, Set $set) {
                        $set('categories',Category::calcRightCategories($state));
                    })
                    ->enableBranchNode()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('location')
                    ->relationship(titleAttribute: 'name')
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('location.name'),
                Tables\Columns\TextColumn::make('amount')
                    ->currency(fn (Budget $record) => $record->currency->name)
            ])
            ->filters([
                DateRangeFilter::make('date'),
                Tables\Filters\Filter::make('description')
                    ->form([
                        Forms\Components\TextInput::make('search')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['search'],
                                fn (Builder $query, $date): Builder => $query
                                    ->where('description', 'LIKE', "%{$data['search']}%")
                            );
                    }),
                Tables\Filters\Filter::make('categories')
                    ->form([
                        SelectTree::make('values')
                            ->label('Excluded categories')
                            ->relationship('categories','name','parent_category_id')
                            ->enableBranchNode()
                            ->independent(false)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['values'],
                                fn (Builder $query, $date): Builder => $query->whereDoesntHave('categories',
                                fn (Builder $query) => $query->whereIn('categories.id', $data['values']))
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
