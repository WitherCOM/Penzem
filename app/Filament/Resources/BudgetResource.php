<?php

namespace App\Filament\Resources;

use App\Enums\Frequency;
use App\Enums\Type;
use App\Filament\Resources\BudgetResource\Pages;
use App\Filament\Resources\BudgetResource\RelationManagers\ChildBudgetsRelationManager;
use App\Models\Budget;
use App\Models\Category;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                Forms\Components\TextInput::make('amount'),
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
                        $categoryIds = collect([]);
                        foreach($state as $category_id)
                        {
                            $categoryModel = Category::find($category_id);
                            $validId = true;
                            foreach ($state as $category_id2)
                            {
                                if ($categoryModel->getChildIds()->contains($category_id2))
                                {
                                    $validId = false;
                                    break;
                                }
                            }
                            if ($validId)
                            {
                                $categoryIds->push($category_id);
                            }
                        }
                        $set('categories',$categoryIds->toArray());
                    })
                    ->enableBranchNode()
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('location')
                    ->relationship(titleAttribute: 'name')
                    ->searchable()
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('parent_budget_id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('categories.name')
                    ->getStateUsing(function(Budget $budget) {
                        $categoryNames = $budget->categories()->pluck('name');
                        foreach($budget->child_budgets as $cBudget)
                        {
                            $categoryNames = $categoryNames->merge($cBudget->categories()->pluck('name'));
                        }

                        return $categoryNames;
                    })
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('location.name'),
                Tables\Columns\TextColumn::make('top_amount')
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
                                    ->orWhereRelation('child_budgets', 'description', 'LIKE', "%{$data['search']}%"),
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
        return [
            ChildBudgetsRelationManager::class
        ];
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
